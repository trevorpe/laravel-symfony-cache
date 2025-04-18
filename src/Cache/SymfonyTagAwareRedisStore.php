<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

class SymfonyTagAwareRedisStore extends SymfonyTagAwareCacheStore
{
    use SymfonyRedisCacheTrait;

    public function __construct(Factory $redis, ?string $prefix = null, ?string $connection = null)
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
        $this->setConnection($connection);

        parent::__construct($this->createRedisAdapter());
    }

    private function createRedisAdapter(): RedisTagAwareAdapter
    {
        return new RedisTagAwareAdapter($this->client(), $this->getPrefix());
    }
}
