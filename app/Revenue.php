<?php

namespace App;

use App\Concerns\AddsMoneyToAccount;
use App\Concerns\CanBePaid;
use App\Concerns\HasVouchers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Revenue extends Model implements AuditableContract
{
    use Auditable, AddsMoneyToAccount, CanBePaid, SoftDeletes, HasVouchers;

    protected $fillable = [
        'account_id',
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

    public function addToAccount()
    {
        $this->addMoneyToAccount(
            $this->amount,
            $this->account
        );
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_email', 'email');
    }
}
