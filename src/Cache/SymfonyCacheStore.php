<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Illuminate\Contracts\Cache\Store;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class SymfonyCacheStore implements Store
{
    use SymfonyCacheTrait;

    public function __construct(AdapterInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }
}
