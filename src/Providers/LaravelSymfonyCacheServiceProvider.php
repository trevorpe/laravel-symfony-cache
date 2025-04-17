<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheAdapterFactory;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheStoreFactory;

class LaravelSymfonyCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SymfonyCacheAdapterFactory::class);

        $this->app->singleton(SymfonyCacheStoreFactory::class);

        $this->app->booting(function () {
            Cache::extend(
                'symfony',
                function (Application $app, array $config) {
                    /** @var SymfonyCacheStoreFactory $factory */
                    $factory = $app->make(SymfonyCacheStoreFactory::class);

                    return $factory->make($config);
                }
            );
        });
    }
}
