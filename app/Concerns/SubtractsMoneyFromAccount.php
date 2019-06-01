<?php


namespace App\Concerns;


use App\Account;
use App\Billing\Exceptions\WithdrawFailed;
use App\Events\MoneySubtracted;
use App\Events\PointsAdded;
use App\User;
use Illuminate\Support\Facades\Auth;
use function points_for;
use Throwable;

trait SubtractsMoneyFromAccount
{

    /**
     * Dispatch the event for subtracting from the account
     *
     * @see AccountProjector
     * @param int $amount
     * @param Account $account
     */
    protected function subtractMoneyFromAccount(int $amount, Account $account): void
    {
        event(
            new MoneySubtracted(
                $account->uuid,
                $amount,
                User::class,
                Auth::id()
            )
        );
    }
}
