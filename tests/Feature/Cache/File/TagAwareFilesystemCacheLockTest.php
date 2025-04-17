<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Tests\Feature\Cache\CacheLockTestCase;

class TagAwareFilesystemCacheLockTest extends CacheLockTestCase
{
    protected function cacheRepository(): Repository
    {
        return $this->cacheRepository ??= $this->factory->make([
            'driver' => 'symfony',
            'adapter' => FilesystemTagAwareAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);
    }
}
