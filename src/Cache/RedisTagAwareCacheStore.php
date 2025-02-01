<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

class RedisTagAwareCacheStore extends SymfonyTagAwareCacheStore
{
    private string $prefix;

    /**
     * @param \Redis|\Relay\Relay|\RedisArray|\RedisCluster|\Predis\ClientInterface $redis
     * @param string $prefix
     */
    public function __construct($redis, string $prefix = '')
    {
        $this->prefix = $prefix;

        parent::__construct(
            new RedisTagAwareAdapter($redis, $prefix)
        );
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
}
