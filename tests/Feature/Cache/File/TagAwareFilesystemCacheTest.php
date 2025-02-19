<?php

namespace Tests\Feature\Cache\File;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Cache\TaggedCacheTestCase;

class TagAwareFilesystemCacheTest extends TaggedCacheTestCase
{

    protected function laravelCache(): Repository
    {
        // The Laravel file store is not taggable, so we just use an array to compare tagging behavior
        return Cache::store('array');
    }

    protected function symfonyCache(): Repository
    {
        return Cache::store('symfony_tag_aware_file');
    }
}
