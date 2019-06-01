<?php


namespace App\Concerns;


use App\Account;
use App\Events\MoneyAdded;
use App\User;
use Illuminate\Support\Facades\Auth;

trait AddsMoneyToAccount
{
    /**
     * Dispatch the event for adding to the Account
     *
     * @see AccountProjector
     * @param int $amount
     * @param Account $account
     */
    protected function addMoneyToAccount(int $amount, Account $account)
    {
        event(
            new MoneyAdded(
                $account->uuid,
                $amount,
                User::class,
                Auth::id()
            )
        );
    }
}
