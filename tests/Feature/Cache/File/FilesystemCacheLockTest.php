<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tests\Feature\Cache\CacheLockTestCase;

class FilesystemCacheLockTest extends CacheLockTestCase
{
    protected function cacheRepository(): Repository
    {
        return $this->cacheRepository ??= $this->factory->make([
            'driver' => 'symfony',
            'adapter' => FilesystemAdapter::class,
            'path' => storage_path('framework/cache/data'),
        ]);
    }
}
