<?php

declare(strict_types=1);

namespace OrthancTower\Client;

use Illuminate\Support\ServiceProvider;
use OrthancTower\Client\Commands\StatusCommand;
use OrthancTower\Client\Commands\TestConnectionCommand;
use OrthancTower\Client\Contracts\OrthancClientContract;

/**
 * @see https://laravel.com/docs/12.x/providers
 */
class OrthancClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @see https://laravel.com/docs/12.x/providers#the-register-method
     */
    public function register(): void
    {
        // 1. Merge package config (published or bundled)
        $this->mergeConfigFrom(
            __DIR__.'/../config/orthanc-client.php',
            'orthanc-client'
        );

        // 2. Register OrthancClient singleton
        $this->app->singleton('orthanc-client', function ($app) {
            return new OrthancClient;
        });

        $this->app->alias('orthanc-client', OrthancClient::class);
        $this->app->alias('orthanc-client', OrthancClientContract::class);

        // 3. Optional: Override Laravel exception handler for auto-reporting
        // Only if explicitly enabled in config
        if (config('orthanc-client.auto_report_exceptions', true)
            && config('orthanc-client.override_exception_handler', false)) {

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
     *
     * @see https://laravel.com/docs/12.x/providers#the-boot-method
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/orthanc-client.php' => config_path('orthanc-client.php'),
        ], 'orthanc-config');  // ✅ Consistent tag

        // Register Artisan commands (console only)
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestConnectionCommand::class,
                StatusCommand::class,
            ]);
        }
    }
}
