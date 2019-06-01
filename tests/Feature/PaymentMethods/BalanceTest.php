<?php

namespace Tests\Feature\PaymentMethods;

use App\Account;
use App\AccountLedger;
use App\Billing\PaymentMethod;
use App\Billing\PaymentMethodOptions;
use App\Billing\PaymentMethods\Balance;
use App\CurrencyRate;
use App\Enums\PaymentCategory;
use App\Enums\RevenueCategory;
use App\Enums\VoucherType;
use App\Revenue;
use App\User;
use App\Voucher;
use function config;
use function date;
use function factory;
use function points_for;
use Tests\Concerns\AssertsAccountLedgerAmount;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BalanceTest extends TestCase
{
    use AssertsAccountLedgerAmount, RefreshDatabase;

    public function testWithdrawCreatesRevenue()
    {
        $customer = factory(User::class)->create();
        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 5000
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        $revenue = PaymentMethod::driver('Balance')->withdraw($account, 3500);

        self::assertDatabaseHas('revenues', [
            'id' => $revenue->id,
            'account_id' => $account->getKey(),
            'customer_email' => $account->owner->email,
            'amount' => 3500,
            'currency_code' => config('currency.default'),
            'currency_rate' => CurrencyRate::latest(config('currency.default'))->getValue(),
            'category' => RevenueCategory::PURCHASE,
            'payment_method' => 'Balance',
            'reference' => null
        ]);
    }

    public function testDepositCreatesRevenue()
    {
        $account = factory(Account::class)->create();

        $depositum = PaymentMethod::driver('Balance')->deposit($account, 5000);

        self::assertDatabaseHas('revenues', [
            'id' => $depositum->id,
            'account_id' => $account->id,
            'amount' => 5000,
            'currency_rate' => CurrencyRate::latest(config('currency.default'))->getValue(),
            'currency_code' => config('currency.default'),
            'description' => null,
            'payment_method' => 'Balance',
            'category' => RevenueCategory::DEPOSITUM,
            'reference' => null
        ]);
    }

    public function testRefundCreatesAPayment()
    {
        $account = factory(Account::class)->create();
        $revenue = PaymentMethod::driver('Balance')->refund(
            $account,
            7400,
            function (PaymentMethodOptions $options) {
                $options
                    ->setCurrencyCode('EUR')
                    ->setCurrencyRate(1.5)
                    ->setDescription('hello world')
                    ->setPaidAt('2019-06-01');
            }
        );

        self::assertDatabaseHas('payments', [
            'id' => $revenue->id,
            'revenue_id' => null,
            'amount' => 7400,
            'category' => PaymentCategory::REFUND,
            'currency_code' => 'EUR',
            'currency_rate' => 1.5,
            'description' => 'hello world',
            'paid_at' => '2019-06-01',
            'payment_method' => 'Balance',
            'reference' => null
        ]);
    }

    public function testCannotRefundBalanceWithoutAnAccount()
    {
        $this->expectException(\InvalidArgumentException::class);

        PaymentMethod::driver('Balance')->refund('john@example.com', 1);
    }

    public function testItWithdrawsFromTheCustomersAccountThenRewardsPoints()
    {
        $customer = factory(User::class)->create();
        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 5000
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        PaymentMethod::driver('Balance')->withdraw($account, 3500);

        $this->assertWithdrawnFromAccountLedgers(
            $account,
            3500,
            5000
        );

        self::assertDatabaseHas('accounts', [
            'uuid' => $uuid,
            'points' => points_for(3500)
        ]);
    }

    public function testItSubtractsTheVoucherAmountFromTheWithdrawnAmount()
    {
        $customer = factory(User::class)->create();
        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 5000
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        $revenue = PaymentMethod::driver('Balance')->withdraw($account, 3500, [
            'vouchers' => [
                new Voucher([
                    'affects_amount' => true,
                    'type' => VoucherType::COMMISSION,
                    'amount' => -75
                ])
            ]
        ]);

        $this->assertWithdrawnFromAccountLedgers(
            $account,
            3500 - 75,
            5000
        );

        self::assertEquals(
            3500,
            $revenue->amount
        );
    }

    public function testItRefundsToTheCustomersAccountThenWithdrawsPoints()
    {
        $customer = factory(User::class)->create();

        $charge = factory(Revenue::class)->create(['amount' => 5000]);

        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 0,
            'points' => points_for(5000)
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        PaymentMethod::driver('Balance')->refund($account, $charge);

        $this->assertAccountLedgersEquals($account, 5000);

        self::assertDatabaseHas('accounts', [
            'uuid' => $uuid,
            'points' => 0
        ]);
    }

    public function testItDepositsToTheCustomersAccount()
    {
        $customer = factory(User::class)->create();

        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 0
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        PaymentMethod::driver('Balance')->deposit($account, 5000);

        $this->assertAccountLedgersEquals($account, 5000);
    }
}
