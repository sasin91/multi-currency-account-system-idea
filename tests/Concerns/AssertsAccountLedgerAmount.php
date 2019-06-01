<?php


namespace Tests\Concerns;


use App\Account;
use App\AccountLedger;
use PHPUnit\Framework\Assert as PHPUnit;

trait AssertsAccountLedgerAmount
{

    protected function assertWithdrawnFromAccountLedgers(Account $account, int $chargedAmount, int $baseBalance): void
    {
        $account->ledgers()->each(function (AccountLedger $ledger) use ($chargedAmount, $baseBalance) {
            $exchangeRate = $ledger->exchangeRate()->getValue();
            $balance = $baseBalance * $exchangeRate;
            $charged = $chargedAmount * $exchangeRate;

            PHPUnit::assertEquals(
                $expected = $balance - $charged, // 1500
                $ledger->balance,
                "Expected [{$ledger->currency}] Ledger to be [{$expected}] but saw [{$ledger->balance}]."
            );
        });
    }

    protected function assertAccountLedgersEquals(Account $account, int $amount): void
    {
        $account->ledgers()->each(function (AccountLedger $ledger) use ($amount) {
            PHPUnit::assertEquals(
                $amount * $ledger->exchangeRate()->getValue(),
                $ledger->balance
            );
        });
    }
}
