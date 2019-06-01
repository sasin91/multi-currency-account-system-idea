<?php


namespace App\Billing;

use App\Billing\PaymentMethods\Balance;
use App\Billing\PaymentMethods\Bank;
use App\Billing\PaymentMethods\Cash;
use App\Billing\PaymentMethods\Points;
use function array_keys;
use Illuminate\Support\Collection;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class PaymentMethodManager extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'balance';
    }

    public function supported(): Collection
    {
        return Collection::make((new ReflectionClass($this))->getMethods())
            ->filter(function (ReflectionMethod $method) {
                return $method->getName() !== 'createDriver'
                    && Str::is('create*Driver', $method->getName());
            })
            ->map(function (ReflectionMethod $method) {
                $methodName = $method->getName();

                $methodName = Str::replaceFirst('create', '', $methodName);
                $methodName = Str::replaceLast('Driver', '', $methodName);

                return $methodName;
            })
            ->merge(array_keys($this->customCreators))
            ->sort();
    }

    public function createCashDriver(): Cash
    {
        return new Cash;
    }

    public function createBankDriver(): Bank
    {
        return new Bank;
    }

    public function createBalanceDriver(): Balance
    {
        return new Balance;
    }

    public function createPointsDriver(): Points
    {
        return new Points;
    }
}
