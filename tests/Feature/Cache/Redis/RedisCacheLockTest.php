<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\CacheLockTestCase;

class RedisCacheLockTest extends CacheLockTestCase
{

    protected function cacheRepository(): Repository
    {
        return Cache::store('symfony_redis');
    }
}
