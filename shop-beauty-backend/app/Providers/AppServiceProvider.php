<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Biz\Auth;
use App\Biz\Enums;
use App\Biz\Salt;

class AppServiceProvider extends ServiceProvider
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
        $this->app->singleton('carbon', function ($app) {
            return new Carbon();
        });

        $this->app->singleton('sauth', function ($app) {
            return new Auth;
        });

        $this->app->singleton('enums', function ($app) {
            return new Enums;
        });

        $this->app->singleton('msalt', function ($app) {
            return new Salt;
        });
    }
}
