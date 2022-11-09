<?php

namespace SmurfWorks\ModelFinder;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ModelFinderProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            ModelFinder::class,
            function () {
                return new ModelFinder;
            }
        );

        $this->app->alias(ModelFinder::class, 'model-finder');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['model-finder'];
    }
}
