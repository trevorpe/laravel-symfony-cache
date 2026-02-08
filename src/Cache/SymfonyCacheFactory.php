<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use TypeError;

class SymfonyCacheFactory
{
    protected Container $container;

    protected SymfonyCacheAdapterFactory $adapterFactory;

    public function __construct(Container $container, SymfonyCacheAdapterFactory $adapterFactory)
    {
        $this->container = $container;
        $this->adapterFactory = $adapterFactory;
    }

    public function repositoryFromConfig(array $config): Repository
    {
        return Cache::repository($this->storeFromConfig($config));
    }

    public function storeFromConfig(array $config): Store
    {
        $desiredAdapter = $config['adapter'];

        if (in_array($desiredAdapter, [RedisAdapter::class, RedisTagAwareAdapter::class])) {
            return $this->storeFromRedisConfig($config);
        }

        $adapter = $this->adapterFactory->createAdapterFromConfig($config);

        return $adapter instanceof TagAwareAdapterInterface
            ? new SymfonyTagAwareCacheStore($adapter)
            : new SymfonyCacheStore($adapter);
    }

    protected function storeFromRedisConfig(array $redisConfig): Store
    {
        $desiredAdapter = $redisConfig['adapter'];
        $tagAware = $redisConfig['tag_aware'] ?? null;

        if (!in_array($desiredAdapter, [RedisAdapter::class, RedisTagAwareAdapter::class])) {
            throw new TypeError("unsupported redis adapter $desiredAdapter provided");
        }

        if ($desiredAdapter === RedisTagAwareAdapter::class || $tagAware) {
            $args = Arr::only($redisConfig, ['adapter', 'connection', 'prefix']);
            return $this->container->make(SymfonyTagAwareRedisStore::class, $args);
        } else {
            $args = Arr::only($redisConfig, ['connection', 'prefix']);
            return $this->container->make(SymfonyRedisStore::class, $args);
        }
    }
}
