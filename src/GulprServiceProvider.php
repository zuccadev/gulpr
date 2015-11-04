<?php

namespace Zuccadev\Gulpr;

use Illuminate\Support\ServiceProvider;

class GulprServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('gulpr', function() {
            return new Gulpr();
        });
    }
}
