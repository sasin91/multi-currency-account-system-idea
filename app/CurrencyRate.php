<?php


namespace App;

use Exchanger\Contract\ExchangeRate;
use Exchanger\CurrencyPair;
use function now;
use Swap\Laravel\Facades\Swap;

class CurrencyRate
{
    public static function latest(string $toCurrency, string $fromCurrency = null): ExchangeRate
    {
        $fromCurrency = $fromCurrency ?? config('currency.default');

        if ($toCurrency === $fromCurrency) {
            return new \Exchanger\ExchangeRate(
                new CurrencyPair($fromCurrency, $toCurrency),
                1,
                now(),
                'custom'
            );
        }

        return Swap::latest("{$fromCurrency}/{$toCurrency}");
    }
}
