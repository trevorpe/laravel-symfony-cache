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
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => FilesystemTagAwareAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);
    }

    public function test_tag_aware_adapter_gets_returned_when_asking_for_inefficient_filesystem()
    {
        $repository = $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => FilesystemTagAwareAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);

        /** @var SymfonyTagAwareCacheStore $store */
        $store = $repository->getStore();

        // We expect it to remap to the more performance Redis adapter
        $this->assertInstanceOf(FilesystemTagAwareAdapter::class, $store->getAdapter());
    }
}
