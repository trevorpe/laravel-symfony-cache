<?php

namespace Tests\Feature\Cache\Array;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\CacheTestCase;

class ArrayCacheTest extends CacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('array');
    }

    protected function symfonyCache(): Repository
    {
        return Cache::store('symfony_array');
    }
}
