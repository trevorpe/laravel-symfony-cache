<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Tests\Feature\Cache\CacheTestCase;

class RedisCacheTest extends CacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('redis');
    }

    protected function symfonyCache(): Repository
    {
        return $this->cacheRepository ??= $this->factory->make([
            'driver' => 'symfony',
            'adapter' => RedisAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony'
        ]);
    }
}
