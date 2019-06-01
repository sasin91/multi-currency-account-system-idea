<?php

namespace Tests\Feature;

use App\Billing;
use Tests\TestCase;

class PaymentMethodManagerTest extends TestCase
{
    public function testItListsTheSupportedDrivers()
    {
        Billing\PaymentMethod::extend('Testing', function () {
            return new class implements Billing\Contracts\PaymentMethod {
                /**
                 * @inheritDoc
                 */
                public function withdraw($accountOrEmail, int $amount, $options = null)
                {
                    // TODO: Implement withdraw() method.
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
            };
        });

        self::assertEquals(
            ['Cash', 'Bank', 'Balance', 'Points', 'Testing'],
            Billing\PaymentMethod::supported()->toArray()
        );
    }
}
