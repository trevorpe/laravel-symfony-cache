<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TagSet;
use Illuminate\Support\Arr;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class TaggedSymfonyCacheStore extends TaggableStore
{
    protected ?TagSet $tags = null;

    protected TagAwareAdapterInterface $cacheAdapter;

    public function __construct(TagAwareAdapterInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
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
        if ($this->tags) {
            $item->tag($this->tags->getNames());
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
            if ($this->tags) {
                $item->tag($this->tags->getNames());
            }

            $this->cacheAdapter->save($item);
        }
        return true;
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
        return $this->cacheAdapter->deleteItem($key);
    }

    public function flush()
    {
        if (!$this->tags || empty($this->tags->getNames())) {
            return $this->cacheAdapter->clear();
        }

        return $this->cacheAdapter->invalidateTags($this->tags->getNames());
    }

    public function invalidateTags($names)
    {
        return $this->cacheAdapter->invalidateTags($names);
    }

    public function getPrefix()
    {
        throw new \BadMethodCallException('this cache store does not implement getPrefix()');
    }

    public function withTags(SymfonyTagSet $tags, ?\Closure $callback = null)
    {
        $this->tags = $tags;

        if ($callback) {
            try {
                return $callback($this);
            } finally {
                $this->tags = null;
            }
        }

        return $this;
    }

    public function tags($names)
    {
        return new SymfonyTaggedCache($this, new SymfonyTagSet($this, is_array($names) ? $names : func_get_args()));
    }
}
