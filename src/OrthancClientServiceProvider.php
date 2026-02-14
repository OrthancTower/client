<?php

declare(strict_types=1);

namespace G80st\OrthancClient;

use G80st\OrthancClient\Commands\StatusCommand;
use G80st\OrthancClient\Commands\TestConnectionCommand;
use Illuminate\Support\ServiceProvider;

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
            return new OrthancClient();
        });

        $this->app->alias('orthanc-client', OrthancClient::class);
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
