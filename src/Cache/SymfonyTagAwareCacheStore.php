<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TagSet;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Trevorpe\LaravelSymfonyCache\Util\CacheKey;

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
        $key = CacheKey::toPsrKey($key);
        $item = $this->cacheAdapter->getItem($key);
        $item->set($value);

        if ($seconds) {
            $item->expiresAfter($seconds);
        }
        if ($this->tags) {
            $item->tag(
                array_map(fn($t) => CacheKey::toPsrKey($t), $this->tags->getNames())
            );
        }

        $this->cacheAdapter->save($item);
        return true;
    }

    public function flush()
    {
        if (!$this->tags || empty($this->tags->getNames())) {
            return $this->cacheAdapter->clear();
        }

        return $this->invalidateTags($this->tags->getNames());
    }

    public function invalidateTags($names)
    {
        $tags = is_array($names) ? $names : func_get_args();

        return $this->cacheAdapter->invalidateTags(
            array_map(fn($t) => CacheKey::toPsrKey($t), $tags)
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
