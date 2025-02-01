<?php

namespace Trevorpe\LaravelSymfonyCache\Cache;

use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;

class FileCacheStore extends TaggedSymfonyCacheStore
{
    private string $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;

        parent::__construct(
            new FilesystemTagAwareAdapter($prefix, 0, storage_path('cache'))
        );
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
}
