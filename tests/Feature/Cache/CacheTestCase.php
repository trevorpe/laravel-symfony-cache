<?php

namespace Tests\Feature\Cache;

use Carbon\CarbonInterval;
use Illuminate\Cache\Repository;
use Tests\TestCase;

/**
 * @extends Repository
 */
class SyncedCache {
    protected Repository $laravel;
    protected Repository $symfony;

    public function __construct(
        Repository $laravel,
        Repository $symfony
    )
    {
        $this->laravel = $laravel;
        $this->symfony = $symfony;
    }

    public function __call(string $name, array $arguments)
    {
        return [
            $this->laravel->{$name}(...$arguments),
            $this->symfony->{$name}(...$arguments)
        ];
    }
}

abstract class CacheTestCase extends TestCase
{
    abstract protected function laravelCache(): Repository;

    abstract protected function symfonyCache(): Repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->syncedCache()->clear();
    }

    /**
     * @return Repository
     */
    protected function syncedCache(): SyncedCache|Repository
    {
        return new SyncedCache($this->laravelCache(), $this->symfonyCache());
    }

    public static function setAndPutProvider(): array
    {
        return [['set'], ['put']];
    }

    public static function deleteAndForgetProvider(): array
    {
        return [['delete'], ['forget']];
    }

    public static function setMultipleAndPutProvider(): array
    {
        return [['setMultiple'], ['put'], ['putMany']];
    }

    public static function rememberForeverAndSearProvider(): array
    {
        return [['rememberForever'], ['sear']];
    }

    public static function hasAndMissingProvider(): array
    {
        return [['has'], ['missing']];
    }

    public static function clearAndFlushProvider(): array
    {
        return [['clear'], ['flush']];
    }

    /*
     * get()/set()/put()
     */
    public function test_get_unset_key()
    {
        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_get_set_key_without_expiry($setMethod)
    {
        $this->syncedCache()->{$setMethod}('abc', 'abc', null);

        $this->assertEquals('abc', $this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_get_set_key_with_expiry_returns_value_before_expiry($setMethod)
    {
        $this->syncedCache()->{$setMethod}('abc', 'abc', 60);

        $this->assertEquals('abc', $this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_get_set_key_with_expiry_returns_null_after_expiry($setMethod)
    {
        $interval = CarbonInterval::microsecond();

        $this->syncedCache()->{$setMethod}('abc', 'abc', $interval);

        usleep(10);

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_get_set_key_with_zero_expiry_returns_null($setMethod)
    {
        $this->syncedCache()->{$setMethod}('abc', 'abc', 0);

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_get_set_key_with_negative_expiry_returns_null($setMethod)
    {
        $this->syncedCache()->{$setMethod}('abc', 'abc', -1);

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /*
     * add()
     */
    public function test_add_adds_unset_key()
    {
        $this->syncedCache()->add('abc', 'abc');

        $this->assertEquals(
            'abc', $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    public function test_add_does_not_overwrite_existing_value()
    {
        $this->syncedCache()->set('abc', 'abc');
        $this->syncedCache()->add('abc', 'not abc');

        $this->assertEquals(
            'abc', $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    public function test_add_does_not_add_if_ttl_is_zero()
    {
        $this->syncedCache()->add('abc', 'abc', 0);

        $this->assertEquals(
            'default', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }

    public function test_add_does_not_add_if_ttl_is_negative()
    {
        $this->syncedCache()->add('abc', 'abc', -1);

        $this->assertEquals(
            'default', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }

    public function test_add_returns_true_when_added()
    {
        $result = $this->syncedCache()->add('abc', 'abc');

        $this->assertTrue($result[0]);
        $this->assertEquals(...$result);
    }

    public function test_add_returns_false_when_not_added()
    {
        $this->syncedCache()->set('abc', 'abc');

        $result = $this->syncedCache()->add('abc', 'abc');

        $this->assertFalse($result[0]);
        $this->assertEquals(...$result);
    }

    public function test_add_returns_false_when_zero_ttl()
    {
        $result = $this->syncedCache()->add('abc', 'abc', 0);

        $this->assertFalse($result[0]);
        $this->assertEquals(...$result);
    }

    public function test_add_returns_false_when_negative_ttl()
    {
        $result = $this->syncedCache()->add('abc', 'abc', -1);

        $this->assertFalse($result[0]);
        $this->assertEquals(...$result);
    }

    /*
     * increment()/decrement()
     */
    public function test_increment_sets_unset_value_to_increment()
    {
        $this->syncedCache()->increment('abc', 10);

        $this->assertEquals(
            10, $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    public function test_increment_increments_existing_value_to_increment()
    {
        $this->syncedCache()->set('abc', 1);
        $this->syncedCache()->increment('abc', 10);

        $this->assertEquals(
            11, $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    public function test_decrement_sets_unset_value_to_decrement()
    {
        $this->syncedCache()->decrement('abc', 10);

        $this->assertEquals(
            -10, $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    public function test_decrement_increments_existing_value_to_decrement()
    {
        $this->syncedCache()->set('abc', 1);
        $this->syncedCache()->decrement('abc', 10);

        $this->assertEquals(
            -9, $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /*
     * forever()
     */
    public function test_forever_adds_value()
    {
        $this->syncedCache()->forever('abc', 'abc');

        $this->assertEquals(
            'abc', $this->laravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /*
     * remember()
     */
    public function test_remember_stores_value_when_ttl_is_positive()
    {
        $this->syncedCache()->remember('abc', 60, fn() => 'abc');

        $this->assertEquals(
            'abc', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }

    public function test_remember_does_not_store_value_when_ttl_is_zero()
    {
        $this->syncedCache()->remember('abc', 0, fn() => 'abc');

        $this->assertEquals(
            'default', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }

    public function test_remember_does_not_store_value_when_ttl_is_negative()
    {
        $this->syncedCache()->remember('abc', -1, fn() => 'abc');

        $this->assertEquals(
            'default', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }

    /*
     * rememberForever()/sear()
     */
    /**
     * @dataProvider rememberForeverAndSearProvider
     */
    public function test_remember_forever_stores_value($rememberMethod)
    {
        $this->syncedCache()->{$rememberMethod}('abc', fn() => 'abc');

        $this->assertEquals(
            'abc', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }

    /**
     * @dataProvider rememberForeverAndSearProvider
     */
    public function test_remember_forever_returns_existing_value_without_executing_closure($rememberMethod)
    {
        $this->syncedCache()->set('abc', 'abc');

        $getterExecuted = false;
        $getter = function () use (&$getterExecuted) {
            $getterExecuted = true;
            return 'abc';
        };

        $result = $this->syncedCache()->{$rememberMethod}('abc', $getter);

        $this->assertEquals(
            'abc', $result[0]
        );

        $this->assertEquals(
            ...$result
        );

        $this->assertFalse($getterExecuted);
    }

    /*
     * delete()
     */
    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_delete_does_nothing_for_unset_key($deleteMethod)
    {
        $this->syncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_delete_forgets_set_key_without_expiry($deleteMethod)
    {
        $this->syncedCache()->set('abc', 'abc', null);

        $this->syncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_delete_forgets_set_key_before_expiry($deleteMethod)
    {
        $this->syncedCache()->set('abc', 'abc', 60);

        $this->syncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_delete_forgets_set_key_after_expiry($deleteMethod)
    {
        $interval = CarbonInterval::microsecond();

        $this->syncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->syncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->laravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->syncedCache()->get('abc')
        );
    }

    /*
     * getMultiple()/setMultiple()
     */
    public function test_get_multiple_returns_default_when_all_unset()
    {
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    public function test_get_multiple_returns_default_for_one_unset()
    {
        $this->syncedCache()->set('abc', 'abc');

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_get_multiple_returns_all_set_values($setMultipleMethod)
    {
        $this->syncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], null);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_get_multiple_returns_only_unexpired_values($setMethod)
    {
        $this->syncedCache()->{$setMethod}('abc', 'abc', 60);
        $this->syncedCache()->{$setMethod}('xyz', 'xyz', -1);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'])
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_set_multiple_sets_expiry_for_all_values($setMultipleMethod)
    {
        $this->syncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 60);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_set_multiple_with_zero_expiry_returns_defaults($setMultipleMethod)
    {
        $this->syncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 0);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_set_multiple_with_negative_expiry_returns_defaults($setMultipleMethod)
    {
        $this->syncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], -1);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /*
     * many()
     */
    public function test_many_returns_null_when_all_unset_and_no_defaults_in_keys()
    {
        $this->assertEquals(
            ['abc' => null, 'xyz' => null],
            $this->laravelCache()->many(['abc', 'xyz'])
        );

        $this->assertEquals(
            ...$this->syncedCache()->many(['abc', 'xyz'])
        );
    }

    public function test_many_returns_default_when_all_unset_and_defaults_in_keys()
    {
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->syncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    public function test_many_returns_default_for_one_unset_when_default_set()
    {
        $this->syncedCache()->set('abc', 'abc');

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->laravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->syncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_many_returns_all_set_values($setMultipleMethod)
    {
        $this->syncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], null);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->laravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->syncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_many_returns_only_unexpired_values($setMethod)
    {
        $this->syncedCache()->{$setMethod}('abc', 'abc', 60);
        $this->syncedCache()->{$setMethod}('xyz', 'xyz', -1);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->laravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->syncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    /*
     * deleteMultiple()
     */
    public function test_delete_multiple_does_nothing_for_unset_data()
    {
        $this->syncedCache()->deleteMultiple(['abc', 'xyz']);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    public function test_delete_multiple_unsets_set_data()
    {
        $this->syncedCache()->set('abc', 'abc');

        $this->syncedCache()->deleteMultiple(['abc', 'xyz']);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    public function test_delete_multiple_unsets_all_set_data()
    {
        $this->syncedCache()->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ]);

        $this->syncedCache()->deleteMultiple(['abc', 'xyz']);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->laravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /*
     * has()/missing()
     */
    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_has_returns_false_for_unset_key($hasMethod)
    {
        $this->assertEquals(
            $hasMethod !== 'has',
            $this->laravelCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_has_returns_true_for_value_without_expiry($hasMethod)
    {
        $this->syncedCache()->set('abc', 'abc');

        $this->assertEquals(
            $hasMethod === 'has',
            $this->laravelCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_has_returns_true_for_before_expiry($hasMethod)
    {
        $this->syncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            $hasMethod === 'has',
            $this->laravelCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_has_returns_false_for_zero_expiry($hasMethod)
    {
        $this->syncedCache()->set('abc', 'abc', 0);

        $this->assertEquals(
            $hasMethod !== 'has',
            $this->laravelCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_has_returns_false_for_negative_expiry($hasMethod)
    {
        $this->syncedCache()->set('abc', 'abc', -1);

        $this->assertEquals(
            $hasMethod !== 'has',
            $this->laravelCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->syncedCache()->{$hasMethod}('abc')
        );
    }

    /*
     * pull()
     */
    public function test_pull_returns_default_for_unset_key()
    {
        $this->assertEquals(
            'default', $this->laravelCache()->pull('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->pull('abc', 'default')
        );
    }

    public function test_pull_returns_and_deletes_before_expiry()
    {
        $this->syncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            'abc', $this->laravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->laravelCache()->has('abc')
        );

        // Reset and test that values are equal
        $this->syncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            ...$this->syncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->syncedCache()->has('abc')
        );
    }

    public function test_pull_returns_default_after_expiry()
    {
        $interval = CarbonInterval::millisecond();

        $this->syncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->assertEquals(
            'default', $this->laravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->laravelCache()->has('abc')
        );

        // Reset and test that values are equal
        $interval = CarbonInterval::millisecond();

        $this->syncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->assertEquals(
            ...$this->syncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->syncedCache()->has('abc')
        );
    }

    public function test_pull_returns_default_with_zero_expiry()
    {
        $this->syncedCache()->set('abc', 'abc', 0);

        usleep(10);

        $this->assertEquals(
            'default', $this->laravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->laravelCache()->has('abc')
        );

        // Reset and test that values are equal
        $this->syncedCache()->set('abc', 'abc', 0);

        usleep(10);

        $this->assertEquals(
            ...$this->syncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->syncedCache()->has('abc')
        );
    }

    public function test_pull_returns_default_with_negative_expiry()
    {
        $this->syncedCache()->set('abc', 'abc', -1);

        usleep(10);

        $this->assertEquals(
            'default', $this->laravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->laravelCache()->has('abc')
        );

        // Reset and test that values are equal
        $this->syncedCache()->set('abc', 'abc', -1);

        usleep(10);

        $this->assertEquals(
            ...$this->syncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->syncedCache()->has('abc')
        );
    }

    /*
     * clear()/flush()
     */
    /**
     * @dataProvider clearAndFlushProvider
     */
    public function test_clear_clears_all_existing_values($clearMethod)
    {
        $this->syncedCache()->set('abc', 'abc');

        $this->syncedCache()->{$clearMethod}();

        $this->assertEquals(
            'default', $this->laravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->syncedCache()->get('abc', 'default')
        );
    }
}
