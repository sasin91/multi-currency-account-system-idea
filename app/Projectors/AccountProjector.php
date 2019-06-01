<?php

namespace App\Projectors;

use App\Account;
use App\AccountLedger;
use App\Events\CreateAccount;
use App\Events\MoneyAdded;
use App\Events\MoneySubtracted;
use App\Events\PointsAdded;
use App\Events\PointsSubtracted;
use Spatie\EventProjector\Projectors\QueuedProjector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

class AccountProjector implements QueuedProjector
{
    use ProjectsEvents;

    /*
     * Here you can specify which event should trigger which method.
     */
    protected $handlesEvents = [
        CreateAccount::class,
        MoneyAdded::class,
        MoneySubtracted::class,
        PointsAdded::class,
        PointsSubtracted::class
    ];

    public function resetState()
    {
        AccountLedger::query()->truncate();
        Account::query()->truncate();
    }

    public function onCreateAccount(CreateAccount $event)
    {
        /** @var Account $account */
        $account = Account::query()->create($event->attributes);

        foreach (config('currency.supported') as $currency) {
            /** @var AccountLedger $ledger */
            $ledger = $account->ledgers()->create([
                'currency' => $currency,
                'balance' => 0
            ]);

            if (isset($event->attributes['balance'])) {
                $ledger->update([
                    'balance' => $event->attributes['balance'] * $ledger->exchangeRate()->getValue()
                ]);
            }
        }
    }

    public function onMoneyAdded(MoneyAdded $event)
    {
        $this->addMoneyToAccountBalance($event);
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $this->subtractMoneyFromAccountBalance($event);
    }

    public function onPointsAdded(PointsAdded $event)
    {
        $this->addPointsToAccount($event);
    }

    public function onPointsSubtracted(PointsSubtracted $event)
    {
        $this->subtractPointsFromAccount($event);
    }

    private function addMoneyToAccountBalance($event)
    {
        $account = Account::findByEvent($event);

        $account->ledgers->each(function (AccountLedger $ledger) use ($event) {
            $exchangeRate = $ledger->exchangeRate()->getValue();

            $ledger->transactions()->create([
               'causer_type' => $event->causerType,
               'causer_id' => $event->causerId,
               'amount' => $amount = ($event->amount * $exchangeRate),
               'exchange_rate' => $exchangeRate
            ]);

            $ledger->balance += $amount;
            $ledger->save();
        });
    }

    private function subtractMoneyFromAccountBalance($event)
    {
        $account = Account::findByEvent($event);

        $account->ledgers->each(function (AccountLedger $ledger) use ($event) {
            $exchangeRate = $ledger->exchangeRate()->getValue();

            $ledger->transactions()->create([
                'causer_type' => $event->causerType,
                'causer_id' => $event->causerId,
                'amount' => $amount = ($event->amount * $exchangeRate),
                'exchange_rate' => $exchangeRate
            ]);

            $ledger->balance -= $amount;
            $ledger->save();
        });
    }

    private function addPointsToAccount($event)
    {
        $account = Account::findByEvent($event);

        $account->points += $event->amount;

        $account->saveOrFail();
    }

    private function subtractPointsFromAccount($event)
    {
        $account = Account::findByEvent($event);

        $account->points -= $event->amount;

        $account->saveOrFail();
    }
}
