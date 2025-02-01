<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Trevorpe\LaravelSymfonyCache\Cache\FileTagAwareCacheStore;
use Trevorpe\LaravelSymfonyCache\Cache\RedisTagAwareCacheStore;

class LaravelSymfonyCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Cache::extend('file', function (Application $app) {
            return Cache::repository(new FileTagAwareCacheStore());
        });

        Cache::extend('redis', function (Application $app) {
            $store = new RedisTagAwareCacheStore(Redis::client());

            return Cache::repository($store);
        });
    }
}
