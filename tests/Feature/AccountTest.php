<?php

namespace Tests\Feature;

use App\Account;
use App\AccountLedger;
use App\Events\MoneyAdded;
use App\User;
use function config;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['currency.supported' => ['DKK']]);
    }

    public function testItCreatesAnAccountAndLedgersForEachSupportedCurrency()
    {
        $uuid = Account::createThroughEventProjector([
            'owner_id' => factory(User::class)->create()->id
        ]);

        $this->assertDatabaseHas('accounts', [
            'uuid' => $uuid
        ]);

        $accountId = Account::query()->where('uuid', $uuid)->value('id');

        foreach (config('currency.supported') as $currency) {
            $this->assertDatabaseHas('account_ledgers', [
                'account_id' => $accountId,
                'currency' => $currency
            ]);
        }
    }

    /**
     * @throws \Exception
     * @depends testItCreatesAnAccountAndLedgersForEachSupportedCurrency
     */
    public function testItCreatesTheAccountLedgersWithGivenBalance()
    {
        $uuid = Account::createThroughEventProjector([
            'owner_id' => factory(User::class)->create()->id,
            'balance' => 100
        ]);

        $account = Account::with('ledgers')->where('uuid', $uuid)->first();

        $account->ledgers->each(function (AccountLedger $ledger) {
            self::assertEquals(
                100 * $ledger->exchangeRate()->getValue(),
                $ledger->balance
            );
        });
    }

    public function testItFindTheAccountByAStoredEvent()
    {
        $account = factory(Account::class)->create();

        $event = new MoneyAdded($account->uuid, 100);

        $findResult = Account::findByEvent($event);

        $this->assertTrue(
            $account->is($findResult),
            "Expected Account[{$account->uuid}] found Account[{$findResult->uuid}]."
        );
    }
}
