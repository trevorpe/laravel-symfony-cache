<?php

namespace Tests;

use Illuminate\Config\Repository;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Trevorpe\LaravelSymfonyCache\Providers\LaravelSymfonyCacheServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelSymfonyCacheServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        /** @var Repository $config */
        $config = $app['config'];

        /*
         * Testable adapters
         */
        $config->set('cache.stores.symfony_redis', [
            'driver' => 'symfony',
            'adapter' => RedisTagAwareAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache')
        ]);

        $config->set('cache.stores.symfony_file', [
            'driver' => 'symfony',
            'adapter' => FilesystemTagAwareAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);
    }
}
