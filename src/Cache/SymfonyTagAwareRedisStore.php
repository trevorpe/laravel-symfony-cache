<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

class SymfonyTagAwareRedisStore extends SymfonyTagAwareCacheStore
{
    use SymfonyRedisCacheTrait {
        __construct as traitConstruct;
    }

    public function __construct(Repository $config, Factory $redis, ?string $prefix = null, ?string $connection = null)
    {
        $this->traitConstruct($config, $redis, $prefix, $connection);

        parent::__construct($this->cacheAdapter);
    }

    private function createRedisAdapter(): RedisTagAwareAdapter
    {
        return new RedisTagAwareAdapter($this->client(), $this->getPrefix());
    }
}
