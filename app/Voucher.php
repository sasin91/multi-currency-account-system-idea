<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'type',
        'amount',
        'affects_amount'
    ];

    /**
     * The model the voucher is attached to
     *
     * @options [Revenue,Payment]
     * @return MorphTo
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
