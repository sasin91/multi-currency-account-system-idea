<?php


namespace App\Billing;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use App\Billing\Contracts\PaymentMethod as Contract;
/**
 * Class PaymentMethod
 * @package App\Billing
 * @mixin PaymentMethodManager
 *
 * @method static PaymentMethodManager extend(string $driver, Closure $callback)
 * @method static Contract driver($name)
 * @method static Collection supported()
 */
class PaymentMethod extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PaymentMethodManager::class;
    }
}
