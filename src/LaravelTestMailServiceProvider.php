<?php

namespace Resohead\LaravelTestMail;

use Illuminate\Support\ServiceProvider;
use Resohead\LaravelTestMail\TestMailCommand;

class LaravelTestMailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-test-mail');

        $this->publishes([
            __DIR__.'/../config/mail-test.php' => config_path('mail-test.php'),
        ], 'config');

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mail-test.php', 'mail-test');

        $this->commands([
            TestMailCommand::class,
        ]);
    }
}
