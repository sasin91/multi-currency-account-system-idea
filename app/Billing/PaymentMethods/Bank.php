<?php


namespace App\Billing\PaymentMethods;

use App\Account;
use App\Billing\Contracts\PaymentMethod;
use App\Billing\PaymentMethodOptions;
use App\Enums\PaymentCategory;
use App\Enums\RevenueCategory;
use App\Payment;
use App\Revenue;
use function class_basename;
use function is_email;
use function is_null;
use function is_object;
use function tap;

class Bank implements PaymentMethod
{
    use Concerns\ResolvesAccount;

    /**
     * @inheritDoc
     */
    public function withdraw($accountOrEmail, int $amount, $options = null)
    {
        $account = $this->resolveAccount($accountOrEmail);

        $options = PaymentMethodOptions::parse($options);

        if (is_email($accountOrEmail) && is_null($options->getCustomerEmail())) {
            $options->setCustomerEmail($accountOrEmail);
        }

        return tap(new Revenue([
            'amount' => $amount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'category' => RevenueCategory::BANK_TRANSFER,
            'payment_method' => class_basename($this),
            'reference' => $options->getReference(),
            'paid_at' => $options->getPaidAt(),
            'description' => $options->getDescription(),
        ]), function (Revenue $revenue) use ($options, $account) {
            if ($account) {
                $revenue->account()->associate($account);
            }

            if ($options->getCustomerEmail()) {
                $revenue->customer_email = $options->getCustomerEmail();
            } elseif ($account && $account->owner) {
                $revenue->customer_email = $account->owner->email;
            }

            $revenue->saveOrFail();

            $revenue->vouchers()->saveMany(
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

        $options = PaymentMethodOptions::parse($options);

        return tap(new Revenue([
            'amount' => $amount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'payment_method' => class_basename($this),
            'category' => RevenueCategory::DEPOSITUM,
            'reference' => $options->getReference(),
            'paid_at' => $options->getPaidAt(),
            'description' => $options->getDescription()
        ]), function (Revenue $depositum) use ($options, $amount, $account) {
            if ($account) {
                $depositum->account()->associate($account);
            }

            if ($options->getCustomerEmail()) {
                $depositum->customer_email = $options->getCustomerEmail();
            } elseif ($account && $account->owner) {
                $depositum->customer()->associate($account->owner);
            }

            $depositum->saveOrFail();

            $depositum->vouchers()->saveMany(
                $options->getVouchers()
            );

            if ($depositum->isPaid()) {
                $depositum->addToAccount();
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function refund($accountOrEmail, $revenueOrAmount, $options = null)
    {
        $account = $this->resolveAccount($accountOrEmail);

        $options = PaymentMethodOptions::parse($options);

        return tap(new Payment([
            'amount' => is_object($revenueOrAmount) ? $revenueOrAmount->amount : $revenueOrAmount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'category' => PaymentCategory::REFUND,
            'payment_method' => class_basename($this),
            'reference' => $options->getReference(),
            'paid_at' => $options->getPaidAt(),
            'description' => $options->getDescription(),
        ]), function (Payment $payment) use ($options, $revenueOrAmount, $account) {
            if ($revenueOrAmount instanceof Revenue) {
                $payment->revenue()->associate($revenueOrAmount);
            }

            if ($account instanceof Account) {
                $payment->account()->associate($account);
            }

            if ($options->getCustomerEmail()) {
                $payment->customer_email = $options->getCustomerEmail();
            } elseif ($account && $account->owner) {
                $payment->customer()->associate($account->owner);
            }

            $payment->saveOrFail();

            $payment->vouchers()->saveMany(
                $options->getVouchers()
            );
        });
    }
}
