<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::store('symfony_redis')->flush();
});

afterEach(function () {
    Cache::store('symfony_redis')->flush();
});

describe('get()', function () {
    it('returns null for missing item', function () {
        $cache = Cache::store('symfony_redis');

        expect($cache->get('abc'))->toBeNull();
        expect($cache->tags('tag')->get('abc'))->toBeNull();
    });

    it('returns value if present', function () {
        $cache = Cache::store('symfony_redis');

        $cache->put('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
        expect($cache->tags('tag')->get('abc'))->toBe('abc');
    });

    it('returns value if present regardless of tags', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->put('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
        expect($cache->tags('tag')->get('abc'))->toBe('abc');
    });

    it('returns value with colon', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->put('a:b:c', 'a:b:c', 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
        expect($cache->tags('t:a:g')->get('a:b:c'))->toBe('a:b:c');
    });
});

describe('getMultiple()', function () {
    it('returns default for all missing keys', function () {
        $cache = Cache::store('symfony_redis');

        $results = $cache->getMultiple(['abc', 'xyz'], 'def');

        expect($results)->toEqual(['abc' => 'def', 'xyz' => 'def']);
    });

    it('returns default for partial missing keys', function () {
        $cache = Cache::store('symfony_redis');

        $cache->put('abc', 'abc');
        $results = $cache->getMultiple(['abc', 'xyz'], 'def');

        expect($results)->toEqual(['abc' => 'abc', 'xyz' => 'def']);
    });

    it('returns values for all available keys', function () {
        $cache = Cache::store('symfony_redis');
        $cache->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ]);

        $results = $cache->getMultiple(['abc', 'xyz']);

        expect($results)->toEqual(['abc' => 'abc', 'xyz' => 'xyz']);
    });

    it('returns values for all available keys with colons', function () {
        $cache = Cache::store('symfony_redis');
        $cache->setMultiple([
            'a:b:c' => 'a:b:c',
            'x:y:z' => 'x:y:z'
        ]);

        $results = $cache->getMultiple(['a:b:c', 'x:y:z']);

        expect($results)->toEqual(['a:b:c' => 'a:b:c', 'x:y:z' => 'x:y:z']);
    });
});

describe('many()', function () {
    it('returns default for all missing keys', function () {
        $cache = Cache::store('symfony_redis');

        $results = $cache->many(['abc' => 'def', 'xyz' => 'def']);

        expect($results)->toEqual(['abc' => 'def', 'xyz' => 'def']);
    });

    it('returns null for partial missing keys', function () {
        $cache = Cache::store('symfony_redis');

        $cache->put('abc', 'abc');
        $results = $cache->many(['abc' => 'def', 'xyz' => 'def']);

        expect($results)->toEqual(['abc' => 'abc', 'xyz' => 'def']);
    });

    it('returns values for all available keys', function () {
        $cache = Cache::store('symfony_redis');
        $cache->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ]);

        $results = $cache->many(['abc', 'xyz']);

        expect($results)->toEqual(['abc' => 'abc', 'xyz' => 'xyz']);
    });

    it('returns values for all available keys with colons', function () {
        $cache = Cache::store('symfony_redis');
        $cache->setMultiple([
            'a:b:c' => 'a:b:c',
            'x:y:z' => 'x:y:z'
        ]);

        $results = $cache->many(['a:b:c', 'x:y:z']);

        expect($results)->toEqual(['a:b:c' => 'a:b:c', 'x:y:z' => 'x:y:z']);
    });
});

describe('set()', function () {
    it('sets the value', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
    });

    it('sets the value with colon in key', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('a:b:c', 'a:b:c', 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
    });

    it('sets the value with tags', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->set('abc', 'abc', 1000);

        expect($cache->get('abc'))->toBe('abc');
    });

    it('sets the value with tags with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->set('a:b:c', 'a:b:c', 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
    });

    it('sets the value forever with null expiry', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('abc', 'abc', null);

        expect($cache->get('abc'))->toBe('abc');
    });

    it('sets the value forever with null expiry with tags', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->set('abc', 'abc');

        expect($cache->get('abc'))->toBe('abc');
    });
});

describe('setMultiple()', function () {
    it('sets many values via associative array', function () {
        $cache = Cache::store('symfony_redis');
        
        $cache->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 1000);

        expect($cache->get('abc'))->toBe('abc');
        expect($cache->get('xyz'))->toBe('xyz');
    });

    it('sets many values via associative array with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->setMultiple([
            'a:b:c' => 'a:b:c',
            'x:y:z' => 'x:y:z'
        ], 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
        expect($cache->get('x:y:z'))->toBe('x:y:z');
    });

    it('sets many values via associative array with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 1000);

        expect($cache->get('abc'))->toBe('abc');
        expect($cache->get('xyz'))->toBe('xyz');
    });

    it('sets many values via associative array with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->setMultiple([
            'a:b:c' => 'a:b:c',
            'x:y:z' => 'x:y:z'
        ], 1000);

        expect($cache->get('a:b:c'))->toBe('a:b:c');
        expect($cache->get('x:y:z'))->toBe('x:y:z');
    });
});

describe('increment()', function() {
    it('sets non-existing', function () {
        $cache = Cache::store('symfony_redis');

        $cache->increment('abc');

        expect($cache->get('abc'))->toBe(1);
    });

    it('sets non-existing with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->increment('a:b:c');

        expect($cache->get('a:b:c'))->toBe(1);
    });

    it('sets non-existing with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->increment('abc');

        expect($cache->get('abc'))->toBe(1);
    });

    it('sets non-existing with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->increment('a:b:c');

        expect($cache->get('a:b:c'))->toBe(1);
    });

    it('increments existing', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('abc', 1, 10000);
        $cache->increment('abc');

        expect($cache->get('abc'))->toBe(2);
    });

    it('increments existing with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('a:b:c', 1, 10000);
        $cache->increment('a:b:c');

        expect($cache->get('a:b:c'))->toBe(2);
    });

    it('increments existing with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->set('abc', 1, 10000);
        $cache->tags('tag')->increment('abc');

        expect($cache->get('abc'))->toBe(2);
    });

    it('increments existing with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->set('a:b:c', 1, 10000);
        $cache->tags('t:a:g')->increment('a:b:c');

        expect($cache->get('a:b:c'))->toBe(2);
    });
});

describe('decrement()', function() {
    it('sets non-existing', function () {
        $cache = Cache::store('symfony_redis');

        $cache->decrement('abc');

        expect($cache->get('abc'))->toBe(-1);
    });

    it('sets non-existing with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->decrement('a:b:c');

        expect($cache->get('a:b:c'))->toBe(-1);
    });

    it('sets non-existing with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->decrement('abc');

        expect($cache->get('abc'))->toBe(-1);
    });

    it('sets non-existing with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->decrement('a:b:c');

        expect($cache->get('a:b:c'))->toBe(-1);
    });

    it('decrements existing', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('abc', 1, 10000);
        $cache->decrement('abc');

        expect($cache->get('abc'))->toBe(0);
    });

    it('decrements existing with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('a:b:c', 1, 10000);
        $cache->decrement('a:b:c');

        expect($cache->get('a:b:c'))->toBe(0);
    });

    it('decrements existing with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->set('abc', 1, 10000);
        $cache->tags('tag')->decrement('abc');

        expect($cache->get('abc'))->toBe(0);
    });

    it('decrements existing with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->set('a:b:c', 1, 10000);
        $cache->tags('t:a:g')->decrement('a:b:c');

        expect($cache->get('a:b:c'))->toBe(0);
    });
});

describe('forever()', function () {
    it('stores value', function () {
        $cache = Cache::store('symfony_redis');

        $cache->forever('abc', 10);

        expect($cache->get('abc'))->toBe(10);
    });

    it('stores value with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->forever('a:b:c', 10);

        expect($cache->get('a:b:c'))->toBe(10);
    });

    it('stores value with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->forever('abc', 10);

        expect($cache->get('abc'))->toBe(10);
    });

    it('stores value with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->forever('a:b:c', 10);

        expect($cache->get('a:b:c'))->toBe(10);
    });
});

describe('forget()', function () {
    it('does nothing for a non-existent key', function () {
        $cache = Cache::store('symfony_redis');

        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('does nothing for a non-existent key with colons', function () {
        $cache = Cache::store('symfony_redis');

        $result = $cache->forget('a:b:c');

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
    });

    it('does nothing for a non-existent key with tag', function () {
        $cache = Cache::store('symfony_redis');

        $result = $cache->tags('tag')->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('does nothing for a non-existent key with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $result = $cache->tags('t:a:g')->forget('a:b:c');

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
    });

    it('forgets an existing key', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('abc', 10, 10000);
        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('forgets an existing key with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('a:b:c', 10, 10000);
        $result = $cache->forget('a:b:c');

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
    });

    it('forgets an existing key with tag', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->set('abc', 10, 10000);
        $result = $cache->forget('abc');

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
    });

    it('forgets an existing key with tag with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->set('a:b:c', 10, 10000);
        $result = $cache->forget('a:b:c');

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
    });
});

describe('flush()', function () {
    it('does nothing if no values', function () {
        $cache = Cache::store('symfony_redis');

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });

    it('clears all values', function () {
        $cache = Cache::store('symfony_redis');

        $cache->set('abc', 10, 10000);
        $cache->forever('xyz', 10);

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });

    it('clears all tagged values when tag is provided', function () {
        $cache = Cache::store('symfony_redis')->tags('tag');

        $cache->set('abc', 10, 10000);
        $cache->forever('xyz', 10);

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });

    it('clears all tagged values when tag is provided with colons', function () {
        $cache = Cache::store('symfony_redis')->tags('t:a:g');

        $cache->set('a:b:c', 10, 10000);
        $cache->forever('x:y:z', 10);

        $result = $cache->flush();

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
        expect($cache->get('x:y:z'))->toBeNull();
    });

    it('only clears tagged values when tag is provided', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag')->set('abc', 'abc', 10000);
        $cache->forever('xyz', 'xyz');

        $result = $cache->tags('tag')->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBe('xyz');
    });

    it('only clears tagged values when tag is provided with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('t:a:g')->set('a:b:c', 'a:b:c', 10000);
        $cache->forever('x:y:z', 'x:y:z');

        $result = $cache->tags('t:a:g')->flush();

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
        expect($cache->get('x:y:z'))->toBe('x:y:z');
    });

    it('clears all tags', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag1')->set('abc', 'abc', 10000);
        $cache->tags('tag2')->forever('xyz', 'xyz');

        $result = $cache->tags('tag1', 'tag2')->flush();

        expect($result)->toBeTrue();
        expect($cache->get('abc'))->toBeNull();
        expect($cache->get('xyz'))->toBeNull();
    });

    it('clears all tags with colons', function () {
        $cache = Cache::store('symfony_redis');

        $cache->tags('tag:1')->set('a:b:c', 'a:b:c', 10000);
        $cache->tags('tag:2')->forever('x:y:z', 'x:y:z');

        $result = $cache->tags('tag:1', 'tag:2')->flush();

        expect($result)->toBeTrue();
        expect($cache->get('a:b:c'))->toBeNull();
        expect($cache->get('x:y:z'))->toBeNull();
    });
});
