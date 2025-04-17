<?php

namespace Tests\Feature\Cache\Array;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Feature\Cache\CacheTestCase;

class ArrayCacheTest extends CacheTestCase
{

    protected function laravelCache(): Repository
    {
        return Cache::store('array');
    }

    protected function symfonyCache(): Repository
    {
        return $this->cacheRepository ??= $this->factory->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => ArrayAdapter::class,
        ]);
    }
}
