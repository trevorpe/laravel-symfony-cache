<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class SymfonyRedisStore extends SymfonyCacheStore
{
    use SymfonyRedisCacheTrait {
        __construct as traitConstruct;
    }

    public function __construct(Repository $config, Factory $redis, ?string $prefix = null, ?string $connection = null)
    {
        $this->traitConstruct($config, $redis, $prefix, $connection);

        parent::__construct($this->cacheAdapter);
    }

    private function createRedisAdapter(): RedisAdapter
    {
        return new RedisAdapter($this->client(), $this->getPrefix());
    }
}
