<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\CacheTestCase;

class FilesystemCacheTest extends CacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('file');
    }

    protected function symfonyCache(): Repository
    {
        return Cache::store('symfony_file');
    }
}
