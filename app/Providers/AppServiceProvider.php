<?php

namespace App\Providers;

use Sentry;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      if (app()->environment('local')) {
        Sentry\init(['dsn' => '']);
    } else {
        Sentry\init(['dsn' => env('SENTRY_DSN')]);
    }
    }
}
