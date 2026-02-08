<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class SymfonyCacheAdapterFactory
{
    protected readonly Container $container;
    protected readonly Repository $config;

    public function __construct(Container $container, Repository $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function createAdapterFromConfig(array $config): AdapterInterface
    {
        $adapter = $config['adapter'] ?? null;

        if (!$adapter || !is_a($adapter, AdapterInterface::class, true)) {
            throw new \ValueError(
                'the `adapter` property must point to a valid Symfony adapter (one implementing ' . AdapterInterface::class . ')'
            );
        }

        $adapterBasename = class_basename($adapter);
        if (!method_exists($this, $method = "create$adapterBasename")) {
            throw new \ValueError("$adapterBasename is not a supported Symfony adapter");
        }

        $adapterInstance = $this->container->call([$this, $method], ['config' => $config]);

        // We decorate the adapter with the tag-aware adapter when requested
        $isTagAware = $config['tag_aware'] ?? false;
        if ($isTagAware && !is_a($adapterInstance, TagAwareAdapterInterface::class)) {
            $adapterInstance = new TagAwareAdapter($adapterInstance);
        }

        return $adapterInstance;
    }

    public function createFilesystemTagAwareAdapter(array $config): FilesystemTagAwareAdapter
    {
        return new FilesystemTagAwareAdapter(
            $this->getPrefix($config),
            $this->getDefaultLifetime($config),
            $this->getCachePath($config)
        );
    }

    public function createFilesystemAdapter(array $config): FilesystemAdapter
    {
        return new FilesystemAdapter(
            $this->getPrefix($config),
            $this->getDefaultLifetime($config),
            $this->getCachePath($config)
        );
    }

    public function createArrayAdapter(array $config): ArrayAdapter
    {
        return new ArrayAdapter(
            $this->getDefaultLifetime($config)
        );
    }

    protected function getRedisClient(array $config)
    {
        return Redis::connection($config['connection'] ?? null)->client();
    }

    protected function getCachePath(array $config): string
    {
        return $config['path'] ?? $this->config['cache.stores.file.path'] ?? '';
    }

    protected function getPrefix(array $config)
    {
        return $config['prefix'] ?? $this->config['cache.prefix'];
    }

    protected function getDefaultLifetime(array $config): int
    {
        return $config['defaultLifetime'] ?? 0;
    }
}
