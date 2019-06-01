<?php

namespace App;

use App\Concerns\HasVouchers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Payment extends Model implements AuditableContract
{
    use Auditable, SoftDeletes, HasVouchers;

    protected $fillable = [
        'account_id',
        'revenue_id',
        'customer_email',
        'amount',
        'currency_rate',
        'currency_code',
        'description',
        'category',
        'payment_method',
        'reference',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'date'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_email', 'email');
    }

    /**
     * The revenue we have refunded from
     *
     * @return BelongsTo
     */
    public function revenue(): BelongsTo
    {
        return $this->belongsTo(Revenue::class, 'revenue_id');
    }
}
