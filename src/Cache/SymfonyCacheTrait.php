<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Trevorpe\LaravelSymfonyCache\Util\CacheKey;

trait SymfonyCacheTrait
{
    protected AdapterInterface $cacheAdapter;

    public function get($key)
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $key = CacheKey::toPsrKey($key);
        $item = $this->cacheAdapter->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    public function many(array $keys)
    {
        $keys = array_map(fn($k) => CacheKey::toPsrKey($k), $keys);

        /** @var iterable<string, CacheItem> $items */
        $items = $this->cacheAdapter->getItems($keys);

        $result = [];
        foreach ($items as $key => $item) {
            $result[$key] = $item->get();
        }

        return $result;
    }

    public function put($key, $value, $seconds)
    {
        $item = $this->cacheAdapter->getItem(CacheKey::toPsrKey($key));
        $item->set($value);

        if ($seconds) {
            $item->expiresAfter($seconds);
        }

        $this->cacheAdapter->save($item);
        return true;
    }

    public function putMany(array $values, $seconds)
    {
        $result = true;

        foreach ($values as $key => $value) {
            $key = CacheKey::toPsrKey($key);
            $result = $result && $this->put($key, $value, $seconds);
        }

        return $result;
    }

    public function increment($key, $value = 1)
    {
        $this->forever(
            $key,
            $value = (int) ($this->get($key) ?? 0) + $value
        );

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
        $key = CacheKey::toPsrKey($key);
        return $this->cacheAdapter->deleteItem($key);
    }

    public function flush()
    {
        return $this->cacheAdapter->clear();
    }

    public function getPrefix()
    {
        throw new \BadMethodCallException('this cache store does not implement getPrefix()');
    }
}
