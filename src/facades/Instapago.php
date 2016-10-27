<?php 

namespace Socialgest\Instapago\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Socialgest\Instapago\Instapago
 */
class Instapago extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Socialgest\Instapago\Instapago';
    }
}