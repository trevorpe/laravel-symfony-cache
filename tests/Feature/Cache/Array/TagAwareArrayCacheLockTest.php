<?php

namespace Tests\Feature\Cache\Array;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\CacheLockTestCase;

class TagAwareArrayCacheLockTest extends CacheLockTestCase
{
    protected function cacheRepository(): Repository
    {
        return Cache::store('symfony_tag_aware_array');
    }
}
