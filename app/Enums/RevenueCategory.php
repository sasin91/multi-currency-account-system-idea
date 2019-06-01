<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RevenueCategory extends Enum
{
    const PURCHASE = 'purchase';
    const DEPOSITUM = 'depositum';
    const BANK_TRANSFER = 'bank transfer';
}
