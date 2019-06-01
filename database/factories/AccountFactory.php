<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Account;
use Faker\Generator as Faker;

$factory->define(Account::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'owner_id' => factory(\App\User::class),
        'type' => \App\Enums\AccountType::MAIN,
        'description' => '',
        'points' => 0
    ];
});
