<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Tests\Feature\Cache\TaggedCacheTestCase;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyRedisStore;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyTagAwareCacheStore;

class TagAwareRedisCacheTest extends TaggedCacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('redis');
    }

    protected function symfonyCache(): Repository
    {
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => RedisTagAwareAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony'
        ]);
    }

    public function test_tag_aware_adapter_gets_returned_when_asking_for_inefficient_redis()
    {
        $repository = $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => RedisAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony',
            'tag_aware' => true
        ]);

        /** @var SymfonyTagAwareCacheStore $store */
        $store = $repository->getStore();

        // We expect it to remap to the more performance Redis adapter
        $this->assertInstanceOf(RedisTagAwareAdapter::class, $store->getAdapter());
    }

    public function test_connection_is_set_to_cache()
    {
        /** @var SymfonyRedisStore $cache */
        $cache = $this->symfonyCache()->getStore();

        $this->assertEquals('cache', $cache->connection()->getName());
    }

    public function test_setting_connection_updates_redis_client()
    {
        /** @var SymfonyRedisStore $cache */
        $cache = $this->symfonyCache()->getStore();

        $cache->setConnection('default');

        $this->assertEquals('default', $cache->connection()->getName());
    }
}
