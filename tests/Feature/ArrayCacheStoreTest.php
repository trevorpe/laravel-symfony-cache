<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::store('symfony_array')->flush();
});

afterEach(function () {
    Cache::store('symfony_array')->flush();
});

describe('get()', function () {
    it('returns null for missing item', function () {
        $cache = Cache::store('symfony_array');

        expect($cache->get('abc'))->toBeNull();
    });

    it('returns value if present', function () {
        $cache = Cache::store('symfony_array');

        $cache->put('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
    });
});

describe('getMultiple()', function () {
    it('returns null for all missing keys', function () {
        $cache = Cache::store('symfony_array');

        $results = $cache->getMultiple(['abc', 'xyz']);

        expect($results)->toEqual(['abc' => null, 'xyz' => null]);
    });

    it('returns null for partial missing keys', function () {
        $cache = Cache::store('symfony_array');

        $cache->put('abc', 'abc');
        $results = $cache->getMultiple(['abc', 'xyz']);

        expect($results)->toEqual(['abc' => 'abc', 'xyz' => null]);
    });

    it('returns values for all available keys', function () {
        $cache = Cache::store('symfony_array');
        $cache->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ]);

        $results = $cache->getMultiple(['abc', 'xyz']);

        expect($results)->toEqual(['abc' => 'abc', 'xyz' => 'xyz']);
    });
});

describe('set()', function () {
    it('sets the value', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
    });

    it('sets the value with colon in key', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('a:b:c', 'a:b:c', 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
    });

    it('sets the value forever with null expiry', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('abc', 'abc', null);

        expect($cache->get('abc'))->toBe('abc');
    });
});

describe('setMultiple()', function () {
    it('sets many values via associative array', function () {
        $cache = Cache::store('symfony_array');

        $cache->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 1000);

        expect($cache->get('abc'))->toBe('abc');
        expect($cache->get('xyz'))->toBe('xyz');
    });

    it('sets many values via associative array with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->setMultiple([
            'a:b:c' => 'a:b:c',
            'x:y:z' => 'x:y:z'
        ], 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
        expect($cache->get('x:y:z'))->toBe('x:y:z');
    });
});

describe('increment()', function() {
    it('sets non-existing', function () {
        $cache = Cache::store('symfony_array');

        $cache->increment('abc');

        expect($cache->get('abc'))->toBe(1);
    });

    it('sets non-existing with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->increment('a:b:c');

        expect($cache->get('a:b:c'))->toBe(1);
    });

    it('increments existing', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('abc', 1, 10000);
        $cache->increment('abc');

        expect($cache->get('abc'))->toBe(2);
    });

    it('increments existing with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('a:b:c', 1, 10000);
        $cache->increment('a:b:c');

        expect($cache->get('a:b:c'))->toBe(2);
    });
});

describe('decrement()', function() {
    it('sets non-existing', function () {
        $cache = Cache::store('symfony_array');

        $cache->decrement('abc');

        expect($cache->get('abc'))->toBe(-1);
    });

    it('sets non-existing with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->decrement('a:b:c');

        expect($cache->get('a:b:c'))->toBe(-1);
    });

    it('decrements existing', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('abc', 1, 10000);
        $cache->decrement('abc');

        expect($cache->get('abc'))->toBe(0);
    });

    it('decrements existing with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('a:b:c', 1, 10000);
        $cache->decrement('a:b:c');

        expect($cache->get('a:b:c'))->toBe(0);
    });
});

describe('forever()', function () {
    it('stores value', function () {
        $cache = Cache::store('symfony_array');

        $cache->forever('abc', 10);

        expect($cache->get('abc'))->toBe(10);
    });

    it('stores value with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->forever('a:b:c', 10);

        expect($cache->get('a:b:c'))->toBe(10);
    });

    it('stores value with tag', function () {
        $cache = Cache::store('symfony_array');

        $cache->forever('abc', 10);

        expect($cache->get('abc'))->toBe(10);
    });

    it('stores value with tag with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->forever('a:b:c', 10);

        expect($cache->get('a:b:c'))->toBe(10);
    });
});

describe('forget()', function () {
    it('does nothing for a non-existent key', function () {
        $cache = Cache::store('symfony_array');

        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('does nothing for a non-existent key with colons', function () {
        $cache = Cache::store('symfony_array');

        $result = $cache->forget('a:b:c');

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
    });

    it('forgets an existing key', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('abc', 10, 10000);
        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('forgets an existing key with colons', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('a:b:c', 10, 10000);
        $result = $cache->forget('a:b:c');

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
    });
});

describe('flush()', function () {
    it('does nothing if no values', function () {
        $cache = Cache::store('symfony_array');

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });

    it('clears all values', function () {
        $cache = Cache::store('symfony_array');

        $cache->set('abc', 10, 10000);
        $cache->forever('xyz', 10);

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });
});
