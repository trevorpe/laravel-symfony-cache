<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheAdapterFactory;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheFactory;

class LaravelSymfonyCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SymfonyCacheAdapterFactory::class);

        $this->app->singleton(SymfonyCacheFactory::class);

        $this->app->booting(function () {
            Cache::extend(
                'symfony',
                function (Application $app, array $config) {
                    /** @var SymfonyCacheFactory $factory */
                    $factory = $app->make(SymfonyCacheFactory::class);

                    return $factory->repositoryFromConfig($config);
                }
            );
        });
    }
}
