<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Arr;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class FileCacheStore extends TaggableStore
{
    private TagAwareAdapterInterface $cacheAdapter;

    public function __construct()
    {
        $this->cacheAdapter = new TagAwareAdapter(
            new FilesystemAdapter('', 0, storage_path('cache'))
        );
    }

    public function get($key)
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $item = $this->cacheAdapter->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    public function many(array $keys)
    {
        /** @var CacheItemInterface[] $items */
        $items = $this->cacheAdapter->getItems($keys);

        return Arr::map($items, fn(CacheItemInterface $item) => $item->isHit() ? $item->get() : null);
    }

    public function put($key, $value, $seconds)
    {
        $item = $this->cacheAdapter->getItem($key);
        $item->set($value);

        if ($seconds) {
            $item->expiresAfter($seconds);
        }

        $this->cacheAdapter->save($item);
        return true;
    }

    public function putMany(array $values, $seconds)
    {
        foreach ($values as $key => $value) {
            $item = $this->cacheAdapter->getItem($key);

            $item->set($value);
            $item->expiresAfter($seconds);

            $this->cacheAdapter->save($item);
        }
        return true;
    }

    public function increment($key, $value = 1)
    {
        $item = $this->cacheAdapter->getItem($key);

        $item->set(
            $value = (int) ($item->get() ?? 0) + $value
        );
        $this->cacheAdapter->save($item);

        return $value;
    }

    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    public function forget($key)
    {
        return $this->cacheAdapter->deleteItem($key);
    }

    public function flush()
    {
        return $this->cacheAdapter->clear();
    }

    public function getPrefix()
    {
        //
    }
}
