<?php

namespace App\Events;

use Spatie\EventProjector\ShouldBeStored;

class PointsAdded implements ShouldBeStored
{
    /**
     * UUID of the Account
     *
     * @var string
     */
    public $accountUuid;

    /**
     * Amount of points to add to the Account
     *
     * @var int
     */
    public $amount;

    /**
     * The class name of the model that caused this event.
     *
     * @options [App\Revenue]
     * @var null|string
     */
    public $causerType;

    /**
     * Primary key of the model that caused this event.
     *
     * @var null|integer
     */
    public $causerId;

    /**
     * PointsAdded constructor.
     *
     * @param string $accountUuid
     * @param int $amount
     * @param string|null $causerType
     * @param string|null $causerId
     */
    public function __construct(string $accountUuid, int $amount, string $causerType = null, string $causerId = null)
    {
        $this->accountUuid = $accountUuid;
        $this->amount = $amount;
        $this->causerType = $causerType;
        $this->causerId = $causerId;
    }
}
