<?php

namespace App\Events;

use Spatie\EventProjector\ShouldBeStored;

class CreateAccount implements ShouldBeStored
{
    /**
     * Account attrs
     *
     * @var array
     */
    public $attributes = [];

    /**
     * CreateAccount constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
