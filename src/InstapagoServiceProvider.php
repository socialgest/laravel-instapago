<?php

namespace Socialgest\Instapago;

use Illuminate\Support\ServiceProvider;

class InstapagoServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/instapago.php' => config_path('instapago.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Socialgest\Instapago\Instapago', function ($app) {
            return new Instapago($app);
        });
    }
}
