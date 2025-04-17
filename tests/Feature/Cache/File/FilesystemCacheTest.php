<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tests\Feature\Cache\CacheTestCase;

class FilesystemCacheTest extends CacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('file');
    }

    protected function symfonyCache(): Repository
    {
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => FilesystemAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);
    }
}
