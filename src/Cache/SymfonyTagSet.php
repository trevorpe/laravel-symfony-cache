<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Cache\TagSet;

class SymfonyTagSet extends TagSet
{
    /** @var TaggedSymfonyCacheStore */
    protected $store;

    public function __construct(TaggedSymfonyCacheStore $store, array $names = [])
    {
        parent::__construct($store, $names);
    }

    public function reset()
    {
        $this->store->withTags(
            $this,
            fn() => $this->store->flush()
        );
    }

    /**
     * Reset the tag and return the new tag identifier.
     *
     * @param  string  $name
     * @return string
     */
    public function resetTag($name)
    {
        $this->store->invalidateTags($name);
        return $name;
    }

    /**
     * Flush all the tags in the set.
     *
     * @return void
     */
    public function flush()
    {
        $this->reset();
    }

    /**
     * Flush the tag from the cache.
     *
     * @param  string  $name
     */
    public function flushTag($name)
    {
        $this->resetTag($name);
    }

    /**
     * Get a unique namespace that changes when any of the tags are flushed.
     *
     * @return string
     */
    public function getNamespace()
    {
        return implode('|', $this->tagIds());
    }

    /**
     * Get the unique tag identifier for a given tag.
     *
     * @param  string  $name
     * @return string
     */
    public function tagId($name)
    {
        return $name;
    }

    /**
     * Get the tag identifier key for a given tag.
     *
     * @param  string  $name
     * @return string
     */
    public function tagKey($name)
    {
        return $name;
    }
}
