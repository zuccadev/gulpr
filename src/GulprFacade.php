<?php

namespace Zuccadev\Gulpr;

use Illuminate\Support\Facades\Facade;

class GulprFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'gulpr'; }
}