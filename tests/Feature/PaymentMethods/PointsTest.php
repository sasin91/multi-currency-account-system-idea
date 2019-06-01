<?php

namespace Tests\Feature\PaymentMethods;

use App\Account;
use App\Billing\PaymentMethod;
use App\Enums\PaymentCategory;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PointsTest extends TestCase
{
    use RefreshDatabase;

    public function testWithdrawsFromCustomerAccount()
    {
        $account = factory(Account::class)->create(['points' => 5000]);

        $payment = PaymentMethod::driver('Points')->withdraw($account, 5000, [
            'description' => 'test'
        ]);

        self::assertEquals($payment->description, 'test');
        self::assertEquals($payment->category, PaymentCategory::POINTS_PURCHASE);

        self::assertDatabaseHas('accounts', [
            'id' => $account->id,
            'points' => 0
        ]);
    }

    public function testDepositAddsToCustomerAccount()
    {
        $account = factory(Account::class)->create(['points' => 0]);

        $payment = PaymentMethod::driver('Points')->deposit($account, 5000, [
            'description' => 'testing'
        ]);

        self::assertEquals($payment->description, 'testing');
        self::assertEquals($payment->category, PaymentCategory::POINTS_DEPOSIT);

        self::assertDatabaseHas('accounts', [
            'id' => $account->id,
            'points' => 5000
        ]);
    }

    public function testRefundAddsToTheCustomerAccount()
    {
        $account = factory(Account::class)->create(['points' => 0]);

        $payment = PaymentMethod::driver('Points')->refund($account, 5000, [
            'description' => 'testing'
        ]);

        self::assertEquals($payment->description, 'testing');
        self::assertEquals($payment->category, PaymentCategory::POINTS_REFUND);

        self::assertDatabaseHas('accounts', [
            'id' => $account->id,
            'points' => 5000
        ]);
    }
}
