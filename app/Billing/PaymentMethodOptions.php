<?php


namespace App\Billing;

use DateTimeInterface;
use App\CurrencyRate;
use App\Voucher;
use Closure;
use function config;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use function is_array;
use function is_string;
use function method_exists;

class PaymentMethodOptions
{
    /**
     * Currency the Transaction uses
     *
     * @var string
     */
    protected $currency;

    /**
     * An optional payment token used by underlying Payment Gateway
     *
     * @var string|null
     */
    protected $paymentToken;

    /**
     * A reference for tracking the Payment or Revenue source.
     * eg. an ID from a Payment Gateway or a Bank Account Identifier
     *
     * @var string|null
     */
    protected $reference;

    /**
     * The currency rate used for converting to other supported currencies
     *
     * @var float|null
     */
    protected $currencyRate;

    /**
     * Email address of the customer we withdraw or deposit from/to
     *
     * @var string|null
     */
    protected $customerEmail;

    /**
     * Vouchers that may affect the amount and gets attached to Model
     *
     * @var array
     */
    protected $vouchers = [];

    /**
     * When the model is paid
     *
     * @var DateTimeInterface|string|null
     */
    protected $paidAt = null;

    /**
     * An optional description of the Model
     *
     * @var null|string
     */
    protected $description = null;

    /**
     * Parse the given options into a PaymentMethodOptions instance.
     *
     * @param Closure|PaymentMethodOptions|array|null $options
     * @return PaymentMethodOptions
     */
    public static function parse($options = [])
    {
        if ($options instanceof PaymentMethodOptions) {
            return $options;
        }

        return new static($options);
    }

    /**
     * PaymentMethodOptions constructor.
     *
     * @param Closure|array $options
     */
    public function __construct($options = [])
    {
        if ($options instanceof Closure) {
            $options($this);
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $method = 'set'.Str::studly($key);
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currency ?? config('currency.default');
    }

    public function getCurrency(): string
    {
        return $this->getCurrencyCode();
    }

    /**
     * @param string $currency
     * @return PaymentMethodOptions
     */
    public function setCurrencyCode(string $currency): PaymentMethodOptions
    {
        $this->currency = $currency;
        return $this;
    }

    public function setCurrency(string $currency): PaymentMethodOptions
    {
        return $this->setCurrencyCode($currency);
    }

    /**
     * @return string|null
     */
    public function getPaymentToken(): ?string
    {
        return $this->paymentToken;
    }

    /**
     * @param string|null $paymentToken
     * @return PaymentMethodOptions
     */
    public function setPaymentToken(?string $paymentToken): PaymentMethodOptions
    {
        $this->paymentToken = $paymentToken;
        return $this;
    }

    /**
     * @return array
     */
    public function getVouchers(): array
    {
        return $this->vouchers;
    }

    /**
     * @param Voucher $voucher
     * @return PaymentMethodOptions
     */
    public function addVoucher(Voucher $voucher): PaymentMethodOptions
    {
        $this->vouchers[] = $voucher;
        return $this;
    }

    /**
     * @param Voucher|array $vouchers
     * @return PaymentMethodOptions
     */
    public function setVouchers($vouchers): PaymentMethodOptions
    {
        $this->vouchers = Arr::wrap($vouchers);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string|null $reference
     * @return PaymentMethodOptions
     */
    public function setReference(?string $reference): PaymentMethodOptions
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCurrencyRate(): ?float
    {
        return $this->currencyRate  ?? CurrencyRate::latest($this->getCurrencyCode())->getValue();
    }

    /**
     * @param float|null $currencyRate
     * @return PaymentMethodOptions
     */
    public function setCurrencyRate(?float $currencyRate): PaymentMethodOptions
    {
        $this->currencyRate = $currencyRate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    /**
     * @param string|null $customerEmail
     * @return PaymentMethodOptions
     */
    public function setCustomerEmail(?string $customerEmail): PaymentMethodOptions
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getPaidAt():?DateTimeInterface
    {
        return $this->paidAt;
    }

    /**
     * @param DateTimeInterface|string|null $paidAt
     * @return PaymentMethodOptions
     */
    public function setPaidAt($paidAt = null): PaymentMethodOptions
    {
        if (is_string($paidAt)) {
            $paidAt = Carbon::parse($paidAt);
        }

        $this->paidAt = $paidAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return PaymentMethodOptions
     */
    public function setDescription(?string $description): PaymentMethodOptions
    {
        $this->description = $description;
        return $this;
    }
}
