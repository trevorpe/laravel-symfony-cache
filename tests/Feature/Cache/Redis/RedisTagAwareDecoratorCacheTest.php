<?php

namespace Tests\Feature\Cache\Redis;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Tests\Feature\Cache\TaggedCacheTestCase;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyRedisStore;

class RedisTagAwareDecoratorCacheTest extends TaggedCacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('redis');
    }

    protected function symfonyCache(): Repository
    {
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => RedisAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'prefix' => 'symfony',
            'tag_aware' => true,
        ]);
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
