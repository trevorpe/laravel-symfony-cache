<?php

namespace Tests\Feature\Cache\Array;

use Illuminate\Cache\Repository;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Feature\Cache\CacheLockTestCase;

class ArrayCacheLockTest extends CacheLockTestCase
{
    protected function cacheRepository(): Repository
    {
        return $this->cacheRepository ??= $this->factory->make([
            'driver' => 'symfony',
            'adapter' => ArrayAdapter::class,
        ]);
    }
}
