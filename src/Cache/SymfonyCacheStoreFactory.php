<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class SymfonyCacheStoreFactory
{
    protected CacheAdapterFactory $adapterFactory;

    public function __construct(CacheAdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function make(array $config): Repository
    {
        $adapter = $this->adapterFactory->createAdapterFromConfig($config);

        $store = $adapter instanceof TagAwareAdapterInterface
            ? new SymfonyTagAwareCacheStore($adapter)
            : new SymfonyCacheStore($adapter);

        return Cache::repository($store);
    }
}
