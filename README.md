[![Test](https://github.com/trevorpe/laravel-symfony-cache/actions/workflows/test.yml/badge.svg)](https://github.com/trevorpe/laravel-symfony-cache/actions/workflows/test.yml)

# laravel-symfony-cache

Use the [Symfony Cache Component](https://symfony.com/components/Cache) with Laravel's cache system. Heavily
inspired by the [alternative-laravel-cache](https://github.com/swayok/alternative-laravel-cache)
project.

> [!CAUTION]
> Symfony's Cache component treats tags differently than Laravel.
> 
> Read the [Tag Behavior](#tag-behavior) section before replacing the default
> Laravel cache stores with this library, and consider how a different tag behavior
> could affect any other libraries that you use.

## Requirements

- Laravel 10.x or greater
- Symfony Cache 7.2 or greater

## Installation

```
composer require trevorpe/laravel-symfony-cache
```

## Usage

This library introduces a new cache driver registered with Laravel's `CacheManager`.

You can use a Symfony cache
adapter by specifying `symfony` as your cache driver alongside an `adapter` property, with the fully-qualified class
name of the Symfony cache adapter you'd like to use.

### Configuration Examples

#### Filesystem

```php
// config/cache.php

return [
    'stores' => [
        'file' => [
            'driver' => 'symfony',
            'adapter' => Symfony\Component\Cache\Adapter\FilesystemAdapter::class,
            'path' => storage_path('framework/cache/data')
        ],
    ]
]
```

#### Filesystem (Tagged)

```php
// config/cache.php

return [
    'stores' => [
        'file' => [
            'driver' => 'symfony',
            'adapter' => Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter::class,
            'path' => storage_path('framework/cache/data')
        ],
    ]
]
```

#### Redis

```php
// config/cache.php

return [
    'stores' => [
        'redis' => [
            'driver' => 'symfony',
            'adapter' => Symfony\Component\Cache\Adapter\RedisAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache')
        ],
    ]
]
```

#### Redis (Tagged)

```php
// config/cache.php

return [
    'stores' => [
        'redis' => [
            'driver' => 'symfony',
            'adapter' => Symfony\Component\Cache\Adapter\RedisTagAwareAdapter::class,
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache')
        ],
    ]
]
```

## Tag Behavior

Similar to [alternative-laravel-cache](https://github.com/swayok/alternative-laravel-cache), this library brings
an alternate tag behavior than Laravel's default cache.

Symfony's cache system does not treat tags as hierarchical like Laravel's cache system does. Instead,
tagging a cache entry with one or more tags allows you to invalidate a group
of entries all at once by referencing its tag. In addition, each tag is applied
individually, rather than as an ordered list. (E.g. applying two tags and calling `flush()` with _either_ of
those tags will invalidate the entry.)

This means that tagging is only really useful when writing or removing data. Reading tagged data will function
identically to getting a cache entry by its key without tags.

### Examples

```php
Cache::set('test-key1', 'test-value1'); // Set the item without tags

Cache::get('test-key1'); // Gets the cache item normally
Cache::tags('tag1')->get('test-key1'); // Gets the cache item normally (unaffected by tags)

Cache::tags('tag1')->flush(); // Flushes nothing, as no values are tagged.
Cache::get('test-key1'); // Value is still present

Cache::flush(); // Flushes all cache entries normally
Cache::get('test-key1'); // Value has been removed

// --------------

Cache::tags('tag2')->set('test-key2', 'test-value2'); // Sets the item with a tag

Cache::get('test-key2'); // Gets the cache item normally
Cache::tags('tag2')->get('test-key2'); // Gets the cache item normally (unaffected by tags)

Cache::tags('tag2')->flush(); // Flushes only entries tagged with 'tag2'
Cache::flush(); // Flushes all cache entries normally

Cache::get('test-key2'); // Value will have been removed by either of the above flush() calls

// --------------

Cache::tags('tag3', 'tag4')->set('test-key3', 'test-value3'); // Sets the item with a tag

Cache::get('test-key3'); // Gets the cache item normally
Cache::tags('tag3')->get('test-key3'); // Unaffected. Gets the cache item normally

Cache::tags('tag3')->flush(); // Flushes only entries tagged with 'tag3'
Cache::tags('tag4')->flush(); // Flushes only entries tagged with 'tag4'
Cache::tags('tag3', 'tag4')->flush(); // Flushes only entries tagged with 'tag3' _or_ 'tag4'
Cache::flush(); // Flushes all cache entries normally

Cache::get('test-key3'); // Value will have been removed by any of the above flush() calls
```
