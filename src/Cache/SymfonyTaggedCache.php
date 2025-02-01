<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Store;

class SymfonyTaggedCache extends TaggedCache
{
    /** @var SymfonyTagSet */
    protected $tags;

    /** @var SymfonyTagAwareCacheStore */
    protected $store;

    /**
     * @param SymfonyTagAwareCacheStore $store
     * @param SymfonyTagSet $tags
     */
    public function __construct(Store $store, SymfonyTagSet $tags)
    {
        parent::__construct($store, $tags);
    }

    public function put($key, $value, $ttl = null)
    {
        return $this->store->withTags(
            $this->tags,
            fn() => parent::put($key, $value, $ttl)
        );
    }

    public function forever($key, $value)
    {
        return $this->store->withTags(
            $this->tags,
            fn() => parent::forever($key, $value)
        );
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->store->withTags(
            $this->tags,
            fn () => parent::increment($key, $value)
        );
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->store->withTags(
            $this->tags,
            fn() => $this->store->decrement($this->itemKey($key), $value)
        );
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->tags->reset();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function itemKey($key)
    {
        return $this->taggedItemKey($key);
    }

    /**
     * Get a fully qualified key for a tagged item.
     *
     * @param  string  $key
     * @return string
     */
    public function taggedItemKey($key)
    {
        return $key;
    }
}
