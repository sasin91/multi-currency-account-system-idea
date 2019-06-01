<?php


namespace App\Exceptions;
use InvalidArgumentException;

class UnsupportedCurrency extends InvalidArgumentException
{
    public static function make(string $currency)
    {
        return new static("[{$currency}] is not supported.");
    }
}
