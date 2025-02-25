<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheStore;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyTagAwareCacheStore;

class LaravelSymfonyCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $serviceProvider = $this;

        $this->app->booting(function () use ($serviceProvider) {
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
        });
    }

    public function createAdapterFromConfig(array $config): AdapterInterface
    {
        $adapter = $config['adapter'] ?? null;

        if (!$adapter || !is_a($adapter, AdapterInterface::class , true)) {
            throw new \ValueError(
                'the `adapter` property must point to a valid Symfony adapter (one implementing ' . AdapterInterface::class . ')'
            );
        }

        // If there are more efficient versions of the requested tag-aware adapter, re-map
        $isTagAware = $config['tag_aware'] ?? false;
        if ($isTagAware && !is_a($adapter, TagAwareAdapterInterface::class)) {
            $adapter = match ($adapter) {
                RedisAdapter::class => RedisTagAwareAdapter::class,
                FilesystemAdapter::class => FilesystemTagAwareAdapter::class,
                default => $adapter
            };
        }

        $adapterBasename = class_basename($adapter);
        if (!method_exists($this, $method = "create$adapterBasename")) {
            throw new \ValueError("$adapterBasename is not a supported Symfony adapter");
        }

        $adapterInstance = $this->app->call([$this, $method], ['config' => $config]);

        // If the adapter was not remapped to a more efficient tag-aware adapter above,
        // we then try to decorate the adapter with the general tag-aware adapter
        if ($isTagAware && !is_a($adapterInstance, TagAwareAdapterInterface::class)) {
            $adapterInstance = new TagAwareAdapter($adapterInstance);
        }

        return $adapterInstance;
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

    public function createArrayAdapter(array $config): ArrayAdapter
    {
        return new ArrayAdapter(
            $this->getDefaultLifetime($config)
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
