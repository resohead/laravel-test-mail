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
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->commands([
            TestMailCommand::class,
        ]);
    }
}
