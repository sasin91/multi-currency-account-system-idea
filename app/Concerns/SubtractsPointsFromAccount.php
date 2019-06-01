<?php


namespace App\Concerns;


use App\Account;
use App\Events\PointsSubtracted;
use App\User;
use Illuminate\Support\Facades\Auth;

trait SubtractsPointsFromAccount
{

    /**
     * Subtract the amount of points from the Account
     *
     * @see AccountProjector
     * @param int $amount
     * @param Account $account
     */
    protected function subtractPointsFromAccount(int $amount, Account $account)
    {
        event(
            new PointsSubtracted(
                $account->uuid,
                $amount,
                User::class,
                Auth::id()
            )
        );
    }
}
