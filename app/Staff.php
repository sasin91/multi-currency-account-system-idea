<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use function in_array;

class Staff
{
    public static $emails = [
        'receptionists' => [
            'contact@iraqiairways.info',
            'reception@iraqiairways.info',
            'reception2@iraqiairways.info',
            'zeinab@iraqiairways.info'
        ],

        'accountants' => [
            'accounting@iraqiairways.info'
        ],

        'developers' => [
            'it@iraqiairways.info',
            'jkh@iraqiairways.info'
        ]
    ];

    public static function check(?Authenticatable $user): bool
    {
        return self::isStaffMember($user);
    }

    public static function isStaffMember(?Authenticatable $user): bool
    {
        if (blank($user)) {
            return false;
        }

        return in_array($user->email, Arr::flatten(self::$emails));
    }

    public static function isReception(?Authenticatable $user): bool
    {
        return in_array($user->email, self::$emails['receptionists']);
    }

    public static function isAccounting(?Authenticatable $user): bool
    {
        return in_array($user->email, self::$emails['accountants']);
    }

    public static function isDeveloper(?Authenticatable $user): bool
    {
        return in_array($user->email, self::$emails['developers']);
    }
}
