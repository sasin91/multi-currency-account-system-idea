<?php

namespace App\Billing\Contracts;

use Closure;
use App\Account;
use App\Billing\PaymentMethodOptions;
use Throwable;
use App\Revenue;
use App\Payment;

interface PaymentMethod
{
    /**
     * Charge the customer then record it in their ledger.
     *
     * @param Account|string $accountOrEmail
     * @param int $amount
     * @param array|Closure|PaymentMethodOptions|null $options
     * @return Revenue
     * @throws Throwable
     */
    public function withdraw($accountOrEmail, int $amount, $options = null);

    /**
     * Charge the customer then add the amount to their ledger
     *
     * @param Account|string $accountOrEmail
     * @param int $amount
     * @param array|Closure|PaymentMethodOptions|null $options
     * @return Revenue
     * @throws Throwable
     */
    public function deposit($accountOrEmail, int $amount, $options = null);

    /**
     * Refund a full Revenue or partial amount to the customer then record it in their ledger.
     *
     * @param Account|string $accountOrEmail
     * @param Revenue|int $revenueOrAmount
     * @param array|Closure|PaymentMethodOptions|null $options
     * @return Payment
     * @throws Throwable
     */
    public function refund($accountOrEmail, $revenueOrAmount, $options = null);
}
