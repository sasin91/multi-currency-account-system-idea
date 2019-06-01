<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Transaction extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'account_ledger_id',
        'causer_type',
        'causer_id',
        'amount',
        'exchange_rate'
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(AccountLedger::class, 'account_ledger_id', 'id');
    }

    /**
     * The model that caused this movement
     *
     * @return MorphTo
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
