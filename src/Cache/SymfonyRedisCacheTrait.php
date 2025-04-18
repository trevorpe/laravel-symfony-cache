<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Contracts\Redis\Factory;
use Symfony\Component\Cache\Adapter\AdapterInterface;

trait SymfonyRedisCacheTrait
{
    protected Factory $redis;

    protected ?string $prefix = null;

    protected ?string $connection = null;

    abstract protected function createRedisAdapter(): AdapterInterface;

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
        $this->cacheAdapter = $this->createRedisAdapter();
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix ?? '';
    }

    public function setPrefix(?string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }
}
