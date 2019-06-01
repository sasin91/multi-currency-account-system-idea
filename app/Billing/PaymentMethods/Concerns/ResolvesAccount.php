<?php


namespace App\Billing\PaymentMethods\Concerns;


use App\Account;
use Throwable;

trait ResolvesAccount
{
    /**
     * @param $account
     * @return Account|null
     * @throws Throwable
     */
    protected function resolveAccount($account):?Account
    {
        if ($account instanceof Account) {
            return $account->loadMissing('ledgers');
        }

        return Account::with('ledgers')->where('uuid', $account)->first();
    }
}
