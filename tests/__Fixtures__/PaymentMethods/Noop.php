<?php


namespace Tests\__Fixtures__\PaymentMethods;

use App\Billing\Contracts\PaymentMethod;
use Tests\Feature\PaymentMethodManagerTest;

/**
 * Class Noop
 * @package Tests\__Fixtures__\PaymentMethods
 * @see PaymentMethodManagerTest#testAutoDiscoveredPaymentMethodsDoesNotOverrideExistingDrivers
 */
class Noop implements PaymentMethod
{
    /**
     * @inheritDoc
     */
    public function withdraw($accountOrEmail, int $amount, $options = null)
    {
        // TODO: Impelement withdraw() method.
    }

    /**
     * @inheritDoc
     */
    public function deposit($accountOrEmail, int $amount, $options = null)
    {
        // TODO: Implement deposit() method.
    }

    /**
     * @inheritDoc
     */
    public function refund($accountOrEmail, $revenueOrAmount, $options = null)
    {
        // TODO: Implement refund() method.
    }
}
