<?php


namespace App\Billing\PaymentMethods\Concerns;


use App\Account;
use InvalidArgumentException;
use function throw_unless;
use Throwable;

trait ValidatesAccount
{
    /**
     * @param Account $account
     * @throws Throwable
     */
    protected function validateAccount($account): void
    {
        throw_unless(
            $account instanceof Account,
            InvalidArgumentException::class
        );
    }
}
