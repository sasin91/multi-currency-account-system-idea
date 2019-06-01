<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentCategory extends Enum
{
    const REFUND = 'refund';
    const POINTS_PURCHASE = 'points purchase';
    const POINTS_DEPOSIT = 'points deposit';
    const POINTS_REFUND = 'points refund';
}
