<?php


namespace App\Billing\PaymentMethods;

use App\AccountLedger;
use App\Billing\Contracts\PaymentMethod;
use App\Billing\Exceptions\WithdrawFailed;
use App\Billing\PaymentMethodOptions;
use App\Concerns\AddsMoneyToAccount;
use App\Concerns\AddsPointsToAccount;
use App\Concerns\SubtractsMoneyFromAccount;
use App\Concerns\SubtractsPointsFromAccount;
use App\Depositum;
use App\Enums\PaymentCategory;
use App\Enums\RevenueCategory;
use App\Payment;
use App\Revenue;
use function class_basename;
use function collect;
use function get_class;
use function is_object;
use function optional;
use function points_for;
use function tap;
use function throw_unless;
use Throwable;

class Balance implements PaymentMethod
{
    use Concerns\ResolvesAccount,
        Concerns\ValidatesAccount,
        AddsPointsToAccount,
        SubtractsPointsFromAccount,
        AddsMoneyToAccount,
        SubtractsMoneyFromAccount;

    /**
     * @inheritDoc
     */
    public function withdraw($accountOrEmail, int $amount, $options = null)
    {
        $account = $this->resolveAccount($accountOrEmail);

        $this->validateAccount($account);

        $options = PaymentMethodOptions::parse($options);

        $effectiveAmount = collect($options->getVouchers())
            ->where('affects_amount', true)
            ->pluck('amount')
            ->reduce(function ($carry, $voucherAmount) {
                return $carry + $voucherAmount;
            }, $amount);

        $this->validateSufficientBalance(
            $effectiveAmount,
            $account->ledgers->firstWhere('currency', $options->getCurrencyCode())
        );

        $this->subtractMoneyFromAccount($effectiveAmount, $account);

        $this->addPointsToAccount(points_for($amount), $account);

        return tap(new Revenue([
            'customer_email' => $options->getCustomerEmail() ?? $account->owner->email,
            'amount' => $amount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'category' => RevenueCategory::PURCHASE,
            'payment_method' => class_basename($this),
            'reference' => $options->getReference(),
            'paid_at' => $options->getPaidAt(),
            'description' => $options->getDescription(),
        ]), function (Revenue $revenue) use ($options, $account) {
            $revenue->account()->associate($account);

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

        $this->validateAccount($account);

        $options = PaymentMethodOptions::parse($options);

        $this->addMoneyToAccount($amount, $account);

        return tap(new Revenue([
            'customer_email' => $options->getCustomerEmail() ?? $account->owner->email,
            'amount' => $amount,
            'currency_code' => $options->getCurrencyCode(),
            'currency_rate' => $options->getCurrencyRate(),
            'payment_method' => class_basename($this),
            'category' => RevenueCategory::DEPOSITUM,
            'reference' => $options->getReference(),
            'paid_at' => $options->getPaidAt(),
            'description' => $options->getDescription(),
        ]), function (Revenue $depositum) use ($options, $amount, $account) {
            $depositum->account()->associate($account);

            $depositum->saveOrFail();

            $depositum->vouchers()->saveMany(
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

        $this->addMoneyToAccount(
            $amount = is_object($revenueOrAmount) ? $revenueOrAmount->amount : $revenueOrAmount,
            $account
        );

        $this->subtractPointsFromAccount(
            points_for($amount),
            $account
        );

        return tap(new Payment([
            'customer_email' => $options->getCustomerEmail() ?? $account->owner->email,
            'amount' => $amount,
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
     * @param AccountLedger $ledger
     * @throws Throwable
     */
    protected function validateSufficientBalance(int $amount, AccountLedger $ledger = null): void
    {
        throw_unless(
            optional($ledger)->balance >= $amount,
            WithdrawFailed::insufficientBalance($amount, $ledger)
        );
    }
}
