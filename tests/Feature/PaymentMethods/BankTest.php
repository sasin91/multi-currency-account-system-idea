<?php

namespace Tests\Feature\PaymentMethods;

use App\Account;
use App\Billing\PaymentMethod;
use App\Billing\PaymentMethodOptions;
use App\Billing\PaymentMethods\Balance;
use App\Billing\PaymentMethods\Bank;
use App\CurrencyRate;
use App\Enums\PaymentCategory;
use App\Enums\RevenueCategory;
use App\User;
use App\Voucher;
use function config;
use function factory;
use function now;
use Tests\Concerns\AssertsAccountLedgerAmount;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankTest extends TestCase
{
    use AssertsAccountLedgerAmount, RefreshDatabase;

    public function testWithdrawCreatesRevenue()
    {
        $revenue = PaymentMethod::driver('Bank')->withdraw(
            'john@example.com',
            7400,
            function (PaymentMethodOptions $options) {
                $options
                    ->setCurrencyCode('EUR')
                    ->setCurrencyRate(1.5)
                    ->setDescription('hello world')
                    ->setPaidAt('2019-06-01');
            }
        );

        self::assertDatabaseHas('revenues', [
            'id' => $revenue->id,
            'amount' => 7400,
            'currency_code' => 'EUR',
            'currency_rate' => 1.5,
            'description' => 'hello world',
            'paid_at' => '2019-06-01',
            'payment_method' => 'Bank',
            'reference' => null
        ]);
    }

    public function testRefundCreatesAPayment()
    {
        $revenue = PaymentMethod::driver('Bank')->refund(
            'john@example.com',
            7400,
            function (PaymentMethodOptions $options) {
                $options
                    ->setCurrencyCode('EUR')
                    ->setCurrencyRate(1.5)
                    ->setDescription('hello world');
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
            'paid_at' => null,
            'payment_method' => 'Bank',
            'reference' => null
        ]);
    }

    public function testDepositCreatesRevenue()
    {
        $revenue = PaymentMethod::driver('Bank')->deposit(
            'john@example.com',
            7400,
            function (PaymentMethodOptions $options) {
                $options
                    ->setCurrencyCode('EUR')
                    ->setCurrencyRate(1.5)
                    ->setDescription('hello world');
            }
        );

        self::assertDatabaseHas('revenues', [
            'id' => $revenue->id,
            'amount' => 7400,
            'currency_code' => 'EUR',
            'currency_rate' => 1.5,
            'description' => 'hello world',
            'paid_at' => null,
            'payment_method' => 'Bank',
            'category' => RevenueCategory::DEPOSITUM,
            'reference' => null
        ]);
    }

    public function testItAddsAPaidDepositumToTheCustomersAccount()
    {
        $customer = factory(User::class)->create();

        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 0
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        PaymentMethod::driver('Bank')->deposit($account, 5000, ['paid_at' => now()]);

        $this->assertAccountLedgersEquals($account, 5000);
    }

    public function testItDoesNotAddAnUnpaidDepositumToTheCustomersAccount()
    {
        $customer = factory(User::class)->create();

        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 0
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        $depositum = PaymentMethod::driver('Bank')->deposit($account, 5000);

        $this->assertAccountLedgersEquals($account, 0);

        self::assertDatabaseHas('revenues', [
            'id' => $depositum->id,
            'account_id' => $account->id,
            'amount' => 5000,
            'currency_rate' => CurrencyRate::latest(config('currency.default'))->getValue(),
            'currency_code' => config('currency.default'),
            'description' => null,
            'payment_method' => 'Bank',
            'category' => RevenueCategory::DEPOSITUM,
            'reference' => null,
            'paid_at' => null
        ]);
    }
}
