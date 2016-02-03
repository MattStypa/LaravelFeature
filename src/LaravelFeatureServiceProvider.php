<?php

namespace Dose\LaravelFeature;

use Dose\Feature\ConnectorInterface;
use Illuminate\Support\ServiceProvider;

class LaravelFeatureServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if (method_exists($this, 'package')) {
            // Laravel 4
            $this->package('dose/laravelfeature', null, __DIR__);
        } else {
            // Laravel 5
            $this->publishes([
                __DIR__ . '/config/config.php' => config_path('laravelfeature/config.php'),
                __DIR__ . '/config/features.php' => config_path('laravelfeature/features.php'),
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            LaravelFeatureInterface::class,
            LaravelFeature::class
        );

        $this->app->singleton(
            ConnectorInterface::class,
            FeatureConnector::class
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            LaravelFeatureInterface::class,
            ConnectorInterface::class,
        ];
    }
}
