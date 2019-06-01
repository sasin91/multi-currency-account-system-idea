<?php

namespace Tests\Feature;

use App\Account;
use App\Billing\PaymentMethod;
use App\Billing\PaymentMethods\Balance;
use App\CurrencyRate;
use App\Enums\RevenueCategory;
use App\Revenue;
use App\User;
use function config;
use function date;
use function factory;
use Tests\Concerns\AssertsAccountLedgerAmount;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RevenueTest extends TestCase
{
    use AssertsAccountLedgerAmount, RefreshDatabase;

    public function testItAddsTheDepositumToTheCustomersAccount()
    {
        $customer = factory(User::class)->create();

        $uuid = Account::createThroughEventProjector([
            'owner_id' => $customer->id,
            'balance' => 0
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        /** @var Revenue $depositum */
        $depositum = factory(Revenue::class)->create([
            'account_id' => $account->id,
            'category' => RevenueCategory::DEPOSITUM,
            'amount' => 5000
        ]);

        $this->assertAccountLedgersEquals($account, 0);

        $depositum->addToAccount();

        $this->assertAccountLedgersEquals($account, 5000);
    }
}
