<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheStore;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyTagAwareCacheStore;

class LaravelSymfonyCacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $serviceProvider = $this;

        Cache::extend(
            'symfony',
            function (Application $app, array $config) use ($serviceProvider) {
                $adapter = $serviceProvider->createAdapterFromConfig($config);

                $store = $adapter instanceof TagAwareAdapterInterface
                    ? new SymfonyTagAwareCacheStore($adapter)
                    : new SymfonyCacheStore($adapter);

                return Cache::repository($store);
            }
        );
    }

    public function createAdapterFromConfig(array $config): AdapterInterface
    {
        $adapter = $config['adapter'] ?? null;

        if (!$adapter || !is_a($adapter, AdapterInterface::class , true)) {
            throw new \ValueError(
                'the `adapter` property must point to a valid Symfony adapter (one implementing ' . AdapterInterface::class . ')'
            );
        }

        $adapterBasename = class_basename($adapter);
        if (!method_exists($this, $method = "create$adapterBasename")) {
            throw new \ValueError("$adapterBasename is not a supported Symfony adapter");
        }

        return $this->app->call([$this, $method], ['config' => $config]);
    }

    public function createRedisTagAwareAdapter(array $config): RedisTagAwareAdapter
    {
        return new RedisTagAwareAdapter(
            $this->getRedisClient($config),
            $this->getPrefix($config),
            $this->getDefaultLifetime($config)
        );
    }

    public function createRedisAdapter(array $config): RedisAdapter
    {
        return new RedisAdapter(
            $this->getRedisClient($config),
            $this->getPrefix($config),
            $this->getDefaultLifetime($config)
        );
    }

    public function createFilesystemTagAwareAdapter(array $config): FilesystemTagAwareAdapter
    {
        return new FilesystemTagAwareAdapter(
            $this->getPrefix($config),
            $this->getDefaultLifetime($config),
            $this->getCachePath($config)
        );
    }

    public function createFilesystemAdapter(array $config): FilesystemAdapter
    {
        return new FilesystemAdapter(
            $this->getPrefix($config),
            $this->getDefaultLifetime($config),
            $this->getCachePath($config)
        );
    }

    protected function getRedisClient(array $config)
    {
        return Redis::connection($config['connection'] ?? null)->client();
    }

    protected function getCachePath(array $config): string
    {
        return $config['path'] ?? $this->app['config']['cache.stores.file.path'] ?? '';
    }

    protected function getPrefix(array $config)
    {
        return $config['prefix'] ?? $this->app['config']['cache.prefix'];
    }

    protected function getDefaultLifetime(array $config): int
    {
        return $config['defaultLifetime'] ?? 0;
    }
}
