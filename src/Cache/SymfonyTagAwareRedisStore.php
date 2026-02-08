<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class SymfonyTagAwareRedisStore extends SymfonyTagAwareCacheStore
{
    use SymfonyRedisCacheTrait {
        __construct as traitConstruct;
    }

    protected string $adapterClass;

    public function __construct(
        Repository $config,
        Factory $redis,
        ?string $adapter = null,
        ?string $prefix = null,
        ?string $connection = null
    )
    {
        $this->adapterClass = $adapter ?? RedisTagAwareAdapter::class;
        $this->traitConstruct($config, $redis, $prefix, $connection);

        parent::__construct($this->cacheAdapter);
    }

    private function createRedisAdapter(): RedisTagAwareAdapter|TagAwareAdapter
    {
        if ($this->adapterClass === RedisTagAwareAdapter::class) {
            return new RedisTagAwareAdapter($this->client(), $this->getPrefix());
        }

        return new TagAwareAdapter(
            new RedisAdapter($this->client(), $this->getPrefix())
        );
    }
}
