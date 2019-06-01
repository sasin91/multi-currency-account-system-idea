<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\AccountLedger;
use Faker\Generator as Faker;

$factory->define(AccountLedger::class, function (Faker $faker) {
    return [
        'account_id' => factory(\App\Account::class),
        'currency' => $faker->randomElement(config('currency.supported')),
        'balance' => 0,
    ];
});
