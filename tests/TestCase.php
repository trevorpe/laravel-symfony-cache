<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
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
            'adapter' => RedisAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony'
        ]);

        $config->set('cache.stores.symfony_tag_aware_redis', [
            'driver' => 'symfony',
            'adapter' => RedisTagAwareAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony'
        ]);

        $config->set('cache.stores.symfony_file', [
            'driver' => 'symfony',
            'adapter' => FilesystemAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);

        $config->set('cache.stores.symfony_tag_aware_file', [
            'driver' => 'symfony',
            'adapter' => FilesystemTagAwareAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);

        $config->set('cache.stores.symfony_array', [
            'driver' => 'symfony',
            'adapter' => ArrayAdapter::class,
        ]);

        $config->set('cache.stores.symfony_tag_aware_array', [
            'driver' => 'symfony',
            'adapter' => ArrayAdapter::class,
            'tag_aware' => true
        ]);

        /*
         * Invalid adapter configurations
         */
        $config->set('cache.stores.symfony_non_cache', [
            'driver' => 'symfony',
            'adapter' => Application::class,
        ]);

        $config->set('cache.stores.symfony_unsupported', [
            'driver' => 'symfony',
            'adapter' => ApcuAdapter::class,
        ]);
    }
}
