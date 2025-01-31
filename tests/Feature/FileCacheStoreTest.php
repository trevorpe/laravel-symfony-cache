<?php

use Trevorpe\LaravelSymfonyCache\Cache\FileCacheStore;

function makeCache(): FileCacheStore
{
    return new FileCacheStore();
}

beforeEach(function () {
    makeCache()->flush();
});

afterEach(function () {
    makeCache()->flush();
});

describe('get()', function () {
    it('returns null for missing item', function () {
        $cache = makeCache();

        expect($cache->get('abc'))->toBeNull();
    });

    it('returns value if present', function () {
        $cache = makeCache();

        $cache->put('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
    });
});

describe('put()', function () {
    it('sets the value', function () {
        $cache = makeCache();

        $cache->put('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
    });

    it('sets the value forever with 0 second expiry', function () {
        $cache = makeCache();

        $cache->put('abc', 'abc', 0);

        expect($cache->get('abc'))->toBe('abc');
    });
});

describe('putMany()', function () {
    it('sets many values via associative array', function () {
        $cache = makeCache();

        $cache->putMany([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 1000);

        expect($cache->get('abc'))->toBe('abc');
        expect($cache->get('xyz'))->toBe('xyz');
    });
});

describe('increment()', function() {
    it('sets non-existing', function () {
        $cache = makeCache();

        $cache->increment('abc');

        expect($cache->get('abc'))->toBe(1);
    });

    it('increments existing', function () {
        $cache = makeCache();

        $cache->put('abc', 1, 10000);
        $cache->increment('abc');

        expect($cache->get('abc'))->toBe(2);
    });
});

describe('decrement()', function() {
    it('sets non-existing', function () {
        $cache = makeCache();

        $cache->decrement('abc');

        expect($cache->get('abc'))->toBe(-1);
    });

    it('increments existing', function () {
        $cache = makeCache();

        $cache->put('abc', 1, 10000);
        $cache->decrement('abc');

        expect($cache->get('abc'))->toBe(0);
    });
});

describe('forever()', function () {
    it('stores value', function () {
        $cache = makeCache();

        $cache->forever('abc', 10);

        expect($cache->get('abc'))->toBe(10);
    });
});

describe('forget()', function () {
    it('does nothing for a non-existent key', function () {
        $cache = makeCache();

        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('forgets an existing key', function () {
        $cache = makeCache();

        $cache->put('abc', 10, 10000);
        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });
});

describe('flush()', function () {
    it('does nothing if no values', function () {
        $cache = makeCache();

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });

    it('clears all values', function () {
        $cache = makeCache();

        $cache->put('abc', 10, 10000);
        $cache->forever('xyz', 10);

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });
});
