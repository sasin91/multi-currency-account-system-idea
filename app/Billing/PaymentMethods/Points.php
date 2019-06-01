<?php


namespace App\Billing\PaymentMethods;


use App\Account;
use App\Billing\Contracts\PaymentMethod;
use App\Billing\Exceptions\WithdrawFailed;
use App\Billing\PaymentMethodOptions;
use App\Concerns\AddsPointsToAccount;
use App\Concerns\SubtractsPointsFromAccount;
use App\Enums\PaymentCategory;
use App\Payment;
use App\Revenue;
use App\User;
use function class_basename;
use function get_class;
use function optional;
use function tap;
use function throw_unless;
use Throwable;

class Points implements PaymentMethod
{
    use AddsPointsToAccount,
        SubtractsPointsFromAccount,
        Concerns\ResolvesAccount,
        Concerns\ValidatesAccount;

    /**
     * @inheritDoc
     */
    public function withdraw($accountOrEmail, int $amount, $options = null)
    {
        $account = $this->resolveAccount($accountOrEmail);

        $this->validateAccount($account);

        $options = PaymentMethodOptions::parse($options);

        $this->validateSufficientPoints(
            $amount,
            $account
        );

        $this->subtractPointsFromAccount(
            $amount,
            $account
        );

        return tap(new Payment([
            'customer_email' => $options->getCustomerEmail() ?? $account->owner->email,
            'amount' => $amount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'category' => PaymentCategory::POINTS_PURCHASE,
            'payment_method' => class_basename($this),
            'reference' => $options->getReference(),
            'description' => $options->getDescription()
        ]), function (Payment $payment) use ($options, $account) {
            $payment->account()->associate($account);
            $payment->customer()->associate($account->owner);

            $payment->saveOrFail();

            $payment->vouchers()->saveMany(
                $options->getVouchers()
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function deposit($accountOrEmail, int $amount, $options = null)
    {
        $account = $this->resolveAccount($accountOrEmail);

        $this->validateAccount($account);

        $options = PaymentMethodOptions::parse($options);

        $this->addPointsToAccount(
            $amount,
            $account
        );

        return tap(new Payment([
            'customer_email' => $options->getCustomerEmail() ?? $account->owner->email,
            'amount' => $amount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'category' => PaymentCategory::POINTS_DEPOSIT,
            'payment_method' => class_basename($this),
            'reference' => $options->getReference(),
            'description' => $options->getDescription()
        ]), function (Payment $payment) use ($options, $account) {
            $payment->account()->associate($account);
            $payment->customer()->associate($account->owner);

            $payment->saveOrFail();

            $payment->vouchers()->saveMany(
                $options->getVouchers()
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function refund($accountOrEmail, $revenueOrAmount, $options = null)
    {
        $account = $this->resolveAccount($accountOrEmail);

        $this->validateAccount($account);

        $options = PaymentMethodOptions::parse($options);

        $this->addPointsToAccount(
            $revenueOrAmount,
            $account
        );

        return tap(new Payment([
            'customer_email' => $options->getCustomerEmail() ?? $account->owner->email,
            'amount' => $revenueOrAmount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'category' => PaymentCategory::POINTS_REFUND,
            'payment_method' => class_basename($this),
            'reference' => $options->getReference(),
            'description' => $options->getDescription()
        ]), function (Payment $payment) use ($options, $account) {
            $payment->account()->associate($account);
            $payment->customer()->associate($account->owner);

            $payment->saveOrFail();

            $payment->vouchers()->saveMany(
                $options->getVouchers()
            );
        });
    }

    /**
     * @param int $amount
     * @param Account $account
     * @throws Throwable
     */
    protected function validateSufficientPoints(int $amount, Account $account): void
    {
        throw_unless(
            $account->points >= $amount,
            WithdrawFailed::insufficientPoints($amount, $account)
        );
    }
}
