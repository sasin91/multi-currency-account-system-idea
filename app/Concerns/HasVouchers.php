<?php


namespace App\Concerns;

use App\Voucher;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVouchers
{

    /**
     * The attached vouchers
     *
     * @return MorphMany
     */
    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'payable');
    }
}
