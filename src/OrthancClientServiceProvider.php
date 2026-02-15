<?php

declare(strict_types=1);

namespace OrthancTower\Client;

use Illuminate\Support\ServiceProvider;
use OrthancTower\Client\Commands\StatusCommand;
use OrthancTower\Client\Commands\TestConnectionCommand;
use OrthancTower\Client\Contracts\OrthancClientContract;

class OrthancClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/orthanc-client.php',
            'orthanc-client'
        );

        $this->app->singleton('orthanc-client', function ($app) {
            return new OrthancClient;
        });
        $this->app->alias('orthanc-client', OrthancClient::class);
        $this->app->alias('orthanc-client', OrthancClientContract::class);

        // Optionally override Laravel's exception handler to auto-report
        if (config('orthanc-client.auto_report_exceptions', true)
            && (bool) config('orthanc-client.override_exception_handler', false)) {
            $this->app->singleton(
                \Illuminate\Contracts\Debug\ExceptionHandler::class,
                \OrthancTower\Client\Exceptions\OrthancClientExceptionHandler::class
            );

            $this->app->singleton(
                \Illuminate\Foundation\Exceptions\Handler::class,
                \OrthancTower\Client\Exceptions\OrthancClientExceptionHandler::class
            );
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/orthanc-client.php' => config_path('orthanc-client.php'),
        ], 'orthanc-client-config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestConnectionCommand::class,
                StatusCommand::class,
            ]);
        }
    }
}
