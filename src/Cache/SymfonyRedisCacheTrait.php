<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\RedisStore;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\AdapterInterface;

trait SymfonyRedisCacheTrait
{
    protected Repository $config;

    protected Factory $redis;

    protected ?string $prefix = null;

    protected ?string $connection = null;

    protected RedisStore $redisStore;

    public function __construct(Repository $config, Factory $redis, ?string $prefix = null, ?string $connection = null)
    {
        $this->config = $config;
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->connection = $connection;

        $this->refreshRedisAdapter();

        $this->redisStore = new RedisStore($this->redis, $this->getPrefix(), $this->connection ?? 'default');
    }

    abstract protected function createRedisAdapter(): AdapterInterface;

    protected function refreshRedisAdapter(): void
    {
        $this->cacheAdapter = $this->createRedisAdapter();
    }

    /**
     * @return mixed|\Redis
     */
    public function client()
    {
        return $this->connection()->client();
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Specify the name of the connection that should be used to store data.
     *
     * @param  string  $connection
     * @return static
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        $this->refreshRedisAdapter();
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix ?? $this->config['cache.prefix'];
    }

    public function setPrefix(?string $prefix): static
    {
        $this->prefix = $prefix;
        $this->refreshRedisAdapter();

        $this->redisStore->setPrefix($prefix);

        return $this;
    }

    /**
     * Get the Redis connection instance that should be used to manage locks.
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function lockConnection()
    {
        return $this->redisStore->lockConnection();
    }
    /**
     * Specify the name of the connection that should be used to manage locks.
     *
     * @param  string  $connection
     * @return $this
     */
    public function setLockConnection($connection)
    {
        $this->redisStore->setLockConnection($connection);
        return $this;
    }

    /**
     * Get a lock instance.
     *
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @return \Illuminate\Contracts\Cache\Lock
     */
    public function lock($name, $seconds = 0, $owner = null)
    {
        return $this->redisStore->lock($name, $seconds, $owner);
    }

    /**
     * Restore a lock instance using the owner identifier.
     *
     * @param  string  $name
     * @param  string  $owner
     * @return \Illuminate\Contracts\Cache\Lock
     */
    public function restoreLock($name, $owner)
    {
        return $this->redisStore->restoreLock($name, $owner);
    }
}
