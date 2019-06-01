<?php

namespace Tests\Feature;

use App\Account;
use App\Events\MoneyAdded;
use App\Events\MoneySubtracted;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountEventsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $accountUuid;
    protected $accountId;

    protected function setUp(): void
    {
        parent::setUp();

        config(['currency.supported' => ['DKK', 'EUR', 'USD']]);

        config([
            'swap.services.array' => [
                [
                    'DKK/DKK' => 1,
                    'DKK/EUR' => 0.13,
                    'DKK/USD' => 0.14,
                ]
            ]
        ]);

        $this->user = factory(User::class)->create();
        $this->accountUuid = Account::createThroughEventProjector([
            'owner_id' => $this->user->id
        ]);
        $this->accountId = Account::query()->where('uuid', $this->accountUuid)->value('id');
    }

    public function testItAddsTheDepositedAmountToEachLedger()
    {
        event(
            new MoneyAdded(
                $this->accountUuid,
                100
            )
        );

        $this->assertDatabaseHas('accounts', [
            'uuid' => $this->accountUuid,
            'points' => 0
        ]);

        $this->assertDatabaseHas('account_ledgers', [
            'account_id' => $this->accountId,
            'currency' => 'DKK',
            'balance' => 100
        ]);

        $this->assertDatabaseHas('account_ledgers', [
            'account_id' => $this->accountId,
            'currency' => 'EUR',
            'balance' => 13
        ]);

        $this->assertDatabaseHas('account_ledgers', [
            'account_id' => $this->accountId,
            'currency' => 'USD',
            'balance' => 14
        ]);
    }

    public function testItSubtractsTheWithdrawnAmountFromEachLedger()
    {
        event(
            new MoneySubtracted(
                $this->accountUuid,
                100
            )
        );

        $this->assertDatabaseHas('accounts', [
            'uuid' => $this->accountUuid,
            'points' => 0
        ]);

        $this->assertDatabaseHas('account_ledgers', [
            'account_id' => $this->accountId,
            'currency' => 'DKK',
            'balance' => -100
        ]);

        $this->assertDatabaseHas('account_ledgers', [
            'account_id' => $this->accountId,
            'currency' => 'EUR',
            'balance' => -13
        ]);

        $this->assertDatabaseHas('account_ledgers', [
            'account_id' => $this->accountId,
            'currency' => 'USD',
            'balance' => -14
        ]);
    }
}
