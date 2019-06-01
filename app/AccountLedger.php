<?php

namespace App;

use Exchanger\Contract\ExchangeRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Swap\Laravel\Facades\Swap;

class AccountLedger extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'account_id',
        'currency',
        'balance',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_ledger_id', 'id');
    }

    public function getCurrencyPairAttribute(): string
    {
        $defaultCurrency = config('currency.default', 'DKK');

        return "{$defaultCurrency}/{$this->currency}";
    }

    public function exchangeRate(): ExchangeRate
    {
        if ($this->asDateTime($this->created_at)->isToday()) {
            return Swap::latest($this->currency_pair);
        }

        return Swap::historical(
            $this->currency_pair,
            $this->created_at
        );
    }
}
