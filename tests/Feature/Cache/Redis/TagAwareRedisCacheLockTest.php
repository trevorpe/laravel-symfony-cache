<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Tests\Feature\Cache\CacheLockTestCase;

class TagAwareRedisCacheLockTest extends CacheLockTestCase
{

    protected function cacheRepository(): Repository
    {
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => RedisTagAwareAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony'
        ]);
    }
}
