<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\TaggedCacheTestCase;

class RedisCacheTest extends TaggedCacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('redis');
    }

    protected function symfonyCache(): Repository
    {
        return Cache::store('symfony_redis');
    }
}
