<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Tests\Feature\Cache\CacheLockTestCase;

class RedisCacheLockTest extends CacheLockTestCase
{

    protected function cacheRepository(): Repository
    {
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => RedisAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony'
        ]);
    }
}
