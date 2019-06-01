<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Billing\PaymentMethod;
use App\CurrencyRate;
use App\Enums\RevenueCategory;
use App\Revenue;
use Faker\Generator as Faker;

$factory->define(Revenue::class, function (Faker $faker) {
    $currency = $faker->randomElement(config('currency.supported'));

    return [
        'customer_email' => null,
        'amount' => $faker->randomDigitNotNull,
        'currency_rate' => CurrencyRate::latest($currency)->getValue(),
        'currency_code' => $currency,
        'description' => null,
        'category' => $faker->randomElement(RevenueCategory::getValues()),
        'payment_method' => $faker->randomElement(PaymentMethod::supported()->toArray()),
        'reference' => null,
        'paid_at' => null
    ];
});
