<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Tests\Feature\Cache\TaggedCacheTestCase;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyTagAwareCacheStore;

class TagAwareRedisCacheTest extends TaggedCacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('redis');
    }

    protected function symfonyCache(): Repository
    {
        return Cache::store('symfony_tag_aware_redis');
    }

    public function test_tag_aware_adapter_gets_returned_when_asking_for_inefficient_redis()
    {
        $repository = Cache::store('symfony_inefficient_tag_aware_redis');

        /** @var SymfonyTagAwareCacheStore $store */
        $store = $repository->getStore();

        // We expect it to remap to the more performance Redis adapter
        $this->assertInstanceOf(RedisTagAwareAdapter::class, $store->getAdapter());
    }
}
