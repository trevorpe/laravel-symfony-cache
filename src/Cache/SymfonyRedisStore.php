<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class SymfonyRedisStore extends SymfonyCacheStore
{
    use SymfonyRedisCacheTrait;

    public function __construct(Factory $redis, string $prefix = '', string $connection = 'default')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
        $this->setConnection($connection);

        parent::__construct($this->createRedisAdapter());
    }

    private function createRedisAdapter(): RedisAdapter
    {
        return new RedisAdapter($this->client(), $this->getPrefix());
    }
}
