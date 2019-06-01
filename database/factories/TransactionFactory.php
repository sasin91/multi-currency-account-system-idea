<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Transaction;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'account_ledger_id' => factory(\App\AccountLedger::class),
        'amount' => $faker->randomDigitNotNull,
        'exchange_rate' => 1
    ];
});
