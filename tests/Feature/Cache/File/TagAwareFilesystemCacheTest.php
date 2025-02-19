<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Tests\Feature\Cache\TaggedCacheTestCase;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyTagAwareCacheStore;

class TagAwareFilesystemCacheTest extends TaggedCacheTestCase
{

    protected function laravelCache(): Repository
    {
        // The Laravel file store is not taggable, so we just use an array to compare tagging behavior
        return Cache::store('array');
    }

    protected function symfonyCache(): Repository
    {
        return Cache::store('symfony_tag_aware_file');
    }

    public function test_tag_aware_adapter_gets_returned_when_asking_for_inefficient_filesystem()
    {
        $repository = Cache::store('symfony_inefficient_tag_aware_file');

        /** @var SymfonyTagAwareCacheStore $store */
        $store = $repository->getStore();

        // We expect it to remap to the more performance Redis adapter
        $this->assertInstanceOf(FilesystemTagAwareAdapter::class, $store->getAdapter());
    }
}
