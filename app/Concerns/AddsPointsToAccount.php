<?php


namespace App\Concerns;


use App\Account;
use App\Events\PointsAdded;
use App\User;
use function event;
use Illuminate\Support\Facades\Auth;

trait AddsPointsToAccount
{
    /**
     * @param int $amount
     * @param Account $account
     */
    protected function addPointsToAccount(int $amount, Account $account): void
    {
        event(
            new PointsAdded(
                $account->uuid,
                $amount,
                User::class,
                Auth::id()
            )
        );
    }
}
