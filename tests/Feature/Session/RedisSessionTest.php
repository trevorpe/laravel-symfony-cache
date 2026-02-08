<?php

namespace Tests\Feature\Session;

use Illuminate\Session\Store;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;


describe('redis session driver', function () {
    beforeEach(function () {
        $this->app['config']->set('session.driver', 'redis');
        $this->app['config']->set('session.store', 'symfony');
    });

    describe('RedisSymfonyStore', function () {
        beforeEach(function () {
            $this->app['config']->set('cache.stores.symfony', [
                'driver' => 'symfony',
                'adapter' => RedisAdapter::class
            ]);
        });

        it('can use symfony driver', function () {
            /** @var Store $session */
            $session = Session::driver('redis');

            $session->put('test', 'test');

            expect($session)->has('test')->toBeTrue();
        });

        it('can use symfony cache with connection', function () {
            Config::set('session.connection', 'cache');

            /** @var Store $session */
            $session = Session::driver('redis');

            $session->put('test', 'test');

            expect($session)->has('test')->toBeTrue();
        });
    });

    describe('SymfonyRedisStore', function () {
        beforeEach(function () {
            $this->app['config']->set('cache.stores.symfony', [
                'driver' => 'symfony',
                'adapter' => RedisAdapter::class
            ]);
        });

        it('can use symfony driver', function () {
            /** @var Store $session */
            $session = Session::driver('redis');

            $session->put('test', 'test');

            expect($session)->has('test')->toBeTrue();
        });

        it('can use symfony cache with connection', function () {
            Config::set('session.connection', 'cache');

            /** @var Store $session */
            $session = Session::driver('redis');

            $session->put('test', 'test');

            expect($session)->has('test')->toBeTrue();
        });
    });

    describe('SymfonyTagAwareRedisStore', function () {
        beforeEach(function () {
            $this->app['config']->set('cache.stores.symfony', [
                'driver' => 'symfony',
                'adapter' => RedisTagAwareAdapter::class
            ]);
        });

        it('can use symfony driver', function () {
            /** @var Store $session */
            $session = Session::driver('redis');

            $session->put('test', 'test');

            expect($session)->has('test')->toBeTrue();
        });

        it('can use symfony cache with connection', function () {
            Config::set('session.connection', 'cache');

            /** @var Store $session */
            $session = Session::driver('redis');

            $session->put('test', 'test');

            expect($session)->has('test')->toBeTrue();
        });
    });
});
