<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class VoucherType extends Enum
{
    const COMMISSION = 'base commission';
    const EXTRA_COMMISSION = 'extra commission';
}
