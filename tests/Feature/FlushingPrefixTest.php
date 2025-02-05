<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::store('redis')->clear();
});

describe('clearing cache with Symfony', function () {
    it('clears Laravel cache with same prefix', function () {
        $laravelRedis = Cache::store('redis');
        $symfonyRedis = Cache::store('symfony_redis');

        $laravelRedis->set('abc', 'abc');

        $symfonyRedis->clear();

        // Symfony Redis has same prefix as Laravel, so it should be cleared
        expect($laravelRedis->has('abc'))->toBeFalse();
    });

    it('does not flush non-default prefix when clearing default prefix', function () {
        $cache1 = Cache::store('symfony_redis_with_prefix');
        $cache2 = Cache::store('symfony_redis');

        $cache1->set('abc', 'abc');

        $cache2->clear();

        // Cache 2's prefix is the default, so it should not clear Cache 1's data
        // since it has a different prefix
        expect($cache1->get('abc'))->toBe('abc');
    });

    it('does not flush default prefix when clearing non-default prefix', function () {
        $cache1 = Cache::store('symfony_redis_with_prefix');
        $cache2 = Cache::store('symfony_redis');

        $cache2->set('abc', 'abc');

        $cache1->clear();

        // Cache 1's prefix is a non-default, so it should not clear Cache 2's data
        // since it has a different prefix
        expect($cache2->get('abc'))->toBe('abc');
    });
});

describe('clearing tagged cache with Symfony', function () {
    it('clears Laravel cache with same tag', function () {
        $laravelRedis = Cache::store('redis')->tags('tag');
        $symfonyRedis = Cache::store('symfony_redis')->tags('tag');

        $laravelRedis->set('abc', 'abc');

        $symfonyRedis->clear();

        // Symfony Redis has same prefix as Laravel, so it should be cleared
        expect($laravelRedis->has('abc'))->toBeFalse();
    });

    it('does not flush non-default prefix when clearing default prefix', function () {
        $cache1 = Cache::store('symfony_redis_with_prefix')->tags('tag');
        $cache2 = Cache::store('symfony_redis')->tags('tag');

        $cache1->set('abc', 'abc');

        $cache2->clear();

        // Cache 2's prefix is the default, so it should not clear Cache 1's data
        // since it has a different prefix
        expect($cache1->get('abc'))->toBe('abc');
    });

    it('does not flush default prefix when clearing non-default prefix', function () {
        $cache1 = Cache::store('symfony_redis_with_prefix')->tags('tag');
        $cache2 = Cache::store('symfony_redis')->tags('tag');

        $cache2->set('abc', 'abc');

        $cache1->clear();

        // Cache 1's prefix is a non-default, so it should not clear Cache 2's data
        // since it has a different prefix
        expect($cache2->get('abc'))->toBe('abc');
    });
});


describe('clearing cache with Laravel', function () {
    it('clears all data', function () {
        $laravelRedis = Cache::store('redis');
        $symfonyRedis = Cache::store('symfony_redis');
        $symfonyPrefixedRedis = Cache::store('symfony_redis_with_prefix');

        $symfonyRedis->set('abc', 'abc');

        $laravelRedis->clear();

        // Laravel's cache behavior is to flush the entire cache
        expect($symfonyRedis->has('abc'))->toBeFalse();
        expect($symfonyPrefixedRedis->has('abc'))->toBeFalse();
        expect($laravelRedis->has('abc'))->toBeFalse();
    });

    it('clears all tagged data', function () {
        $laravelRedis = Cache::store('redis')->tags('tag');
        $symfonyRedis = Cache::store('symfony_redis')->tags('tag');
        $symfonyPrefixedRedis = Cache::store('symfony_redis_with_prefix')->tags('tag');

        $symfonyRedis->set('abc', 'abc');

        $laravelRedis->clear();

        // Laravel's cache behavior is to flush the entire cache
        expect($symfonyRedis->has('abc'))->toBeFalse();
        expect($symfonyPrefixedRedis->has('abc'))->toBeFalse();
        expect($laravelRedis->has('abc'))->toBeFalse();
    });
});
