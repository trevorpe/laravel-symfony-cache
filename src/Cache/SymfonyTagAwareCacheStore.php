<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TagSet;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class SymfonyTagAwareCacheStore extends TaggableStore
{
    use SymfonyCacheTrait;

    protected ?TagSet $tags = null;

    public function __construct(TagAwareAdapterInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
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

    public function flush()
    {
        if (!$this->tags || empty($this->tags->getNames())) {
            return $this->cacheAdapter->clear();
        }

        return $this->cacheAdapter->invalidateTags($this->tags->getNames());
    }

    public function invalidateTags($names)
    {
        return $this->cacheAdapter->invalidateTags(
            is_array($names) ? $names : func_get_args()
        );
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
