<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class SymfonyCacheFactory
{
    protected SymfonyCacheAdapterFactory $adapterFactory;

    public function __construct(SymfonyCacheAdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function repositoryFromConfig(array $config): Repository
    {
        return Cache::repository($this->storeFromConfig($config));
    }

    public function storeFromConfig(array $config): Store
    {
        $adapter = $this->adapterFactory->createAdapterFromConfig($config);

        return $adapter instanceof TagAwareAdapterInterface
            ? new SymfonyTagAwareCacheStore($adapter)
            : new SymfonyCacheStore($adapter);
    }
}
