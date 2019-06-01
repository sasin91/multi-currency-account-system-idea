<?php

use App\Billing\PaymentMethod;
use App\CurrencyRate;
use App\Enums\PaymentCategory;
use App\Payment;
use Faker\Generator as Faker;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

$factory->define(Payment::class, function (Faker $faker) {
    $currency = $faker->randomElement(config('currency.supported'));

    return [
        'customer_email' => null,
        'amount' => $faker->randomDigitNotNull,
        'currency_rate' => CurrencyRate::latest($currency)->getValue(),
        'currency_code' => $currency,
        'description' => null,
        'category' => $faker->randomElement(PaymentCategory::getValues()),
        'payment_method' => $faker->randomElement(PaymentMethod::supported()->toArray()),
        'reference' => null,
        'paid_at' => null
    ];
});
