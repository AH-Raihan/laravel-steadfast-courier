<?php

namespace AhRaihan\SteadfastCourier\Facades;

use AhRaihan\SteadfastCourier\SteadfastClient;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin SteadfastClient
 *
 * @see SteadfastClient
 */
class SteadfastCourier extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'steadfast';
    }
}
