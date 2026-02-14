<?php

declare(strict_types=1);

namespace OrthancTower\Client;

use OrthancTower\Client\Commands\StatusCommand;
use OrthancTower\Client\Commands\TestConnectionCommand;
use OrthancTower\Client\Contracts\OrthancClientContract;
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
        $this->app->alias('orthanc-client', OrthancClientContract::class);
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
