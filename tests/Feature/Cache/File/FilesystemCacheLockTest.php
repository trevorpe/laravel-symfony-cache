<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\CacheLockTestCase;

class FilesystemCacheLockTest extends CacheLockTestCase
{
    protected function cacheRepository(): Repository
    {
        return Cache::store('symfony_file');
    }
}
