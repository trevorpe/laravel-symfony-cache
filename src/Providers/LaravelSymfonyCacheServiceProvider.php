<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Trevorpe\LaravelSymfonyCache\Cache\CacheAdapterFactory;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheStore;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyTagAwareCacheStore;

class LaravelSymfonyCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CacheAdapterFactory::class);

        $this->app->booting(function () {
            Cache::extend(
                'symfony',
                function (Application $app, array $config) {
                    /** @var CacheAdapterFactory $factory */
                    $factory = $app->make(CacheAdapterFactory::class);

                    $adapter = $factory->createAdapterFromConfig($config);

                    $store = $adapter instanceof TagAwareAdapterInterface
                        ? new SymfonyTagAwareCacheStore($adapter)
                        : new SymfonyCacheStore($adapter);

                    return Cache::repository($store);
                }
            );
        });
    }
}
