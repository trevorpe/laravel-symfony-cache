<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\AdapterInterface;

trait SymfonyRedisCacheTrait
{
    protected Repository $config;

    protected Factory $redis;

    protected ?string $prefix = null;

    protected ?string $connection = null;

    public function __construct(Repository $config, Factory $redis, ?string $prefix = null, ?string $connection = null)
    {
        $this->config = $config;
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->connection = $connection;

        $this->refreshRedisAdapter();
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
        return $this;
    }
}
