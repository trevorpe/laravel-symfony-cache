<?php

namespace Trevorpe\LaravelSymfonyCache\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Trevorpe\LaravelSymfonyCache\Cache\FileTagAwareCacheStore;

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
    }
}
