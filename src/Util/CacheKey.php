<?php

namespace Trevorpe\LaravelSymfonyCache\Util;

class CacheKey
{
    public static function toPsrKey(string $key): string
    {
        return str_replace(':', '_colon_', $key);
    }

    public static function fromPsrKey(string $key): string
    {
        return str_replace('_colon_', ':', $key);
    }
}
