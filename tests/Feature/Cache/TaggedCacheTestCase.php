<?php

namespace Tests\Feature\Cache;

use Carbon\CarbonInterval;
use Illuminate\Cache\TaggedCache;

abstract class TaggedCacheTestCase extends CacheTestCase
{
    public function taggedLaravelCache($tags = ['tag']): TaggedCache
    {
        return $this->laravelCache()->tags($tags);
    }

    public function taggedSymfonyCache($tags = ['tag']): TaggedCache
    {
        return $this->symfonyCache()->tags($tags);
    }

    /**
     * @return TaggedCache
     */
    public function taggedSyncedCache($tags = ['tag']): SyncedCache|TaggedCache
    {
        return new SyncedCache(
            $this->taggedLaravelCache($tags),
            $this->taggedSymfonyCache($tags)
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_get_set_key_without_expiry($setMethod)
    {
        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', null);

        $this->assertEquals('abc', $this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // The Symfony cache should also be able to get the value since its tag system isn't
        // hierarchical.
        $this->assertEquals('abc', $this->symfonyCache()->get('abc'));
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_get_set_key_with_expiry_returns_value_before_expiry($setMethod)
    {
        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', 60);

        $this->assertEquals('abc', $this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // The Symfony cache should also be able to get the value since its tag system isn't
        // hierarchical.
        $this->assertEquals('abc', $this->symfonyCache()->get('abc'));
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_get_set_key_with_expiry_returns_null_after_expiry($setMethod)
    {
        $interval = CarbonInterval::microsecond();

        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', $interval);

        usleep(10);

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // We also expect it to be applied without tags on the Symfony side
        $this->assertNull($this->symfonyCache()->get('abc'));
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_get_set_key_with_zero_expiry_returns_null($setMethod)
    {
        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', 0);

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // We also expect it to be applied without tags on the Symfony side
        $this->assertNull($this->symfonyCache()->get('abc'));
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_get_set_key_with_negative_expiry_returns_null($setMethod)
    {
        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', -1);

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // We also expect it to be applied without tags on the Symfony side
        $this->assertNull($this->symfonyCache()->get('abc'));
    }

    /*
     * add()
     */
    public function test_tagged_add_adds_unset_key()
    {
        $this->taggedSyncedCache()->add('abc', 'abc');

        $this->assertEquals(
            'abc', $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            'abc', $this->symfonyCache()->get('abc')
        );
    }

    public function test_tagged_add_does_not_overwrite_existing_value()
    {
        $this->taggedSyncedCache()->set('abc', 'abc');
        $this->taggedSyncedCache()->add('abc', 'not abc');

        $this->assertEquals(
            'abc', $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            'abc', $this->symfonyCache()->get('abc')
        );
    }

    public function test_tagged_add_does_not_add_if_ttl_is_zero()
    {
        $this->taggedSyncedCache()->add('abc', 'abc', 0);

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );

        // Symfony should not have the tag untagged
        $this->assertEquals(
            'default', $this->symfonyCache()->get('abc', 'default')
        );
    }

    public function test_tagged_add_does_not_add_if_ttl_is_negative()
    {
        $this->taggedSyncedCache()->add('abc', 'abc', -1);

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );

        $this->assertEquals(
            'default', $this->symfonyCache()->get('abc', 'default')
        );
    }

    public function test_tagged_add_returns_true_when_added()
    {
        $result = $this->taggedSyncedCache()->add('abc', 'abc');

        $this->assertTrue($result[0]);
        $this->assertEquals(...$result);
    }

    public function test_tagged_add_returns_false_when_not_added()
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $result = $this->taggedSyncedCache()->add('abc', 'abc');

        $this->assertFalse($result[0]);
        $this->assertEquals(...$result);
    }

    public function test_tagged_add_returns_false_when_zero_ttl()
    {
        $result = $this->taggedSyncedCache()->add('abc', 'abc', 0);

        $this->assertFalse($result[0]);
        $this->assertEquals(...$result);
    }

    public function test_tagged_add_returns_false_when_negative_ttl()
    {
        $result = $this->taggedSyncedCache()->add('abc', 'abc', -1);

        $this->assertFalse($result[0]);
        $this->assertEquals(...$result);
    }

    /*
     * increment()/decrement()
     */
    public function test_tagged_increment_sets_unset_value_to_increment()
    {
        $this->taggedSyncedCache()->increment('abc', 10);

        $this->assertEquals(
            10, $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            10, $this->symfonyCache()->get('abc')
        );
    }

    public function test_tagged_increment_increments_existing_value_to_increment()
    {
        $this->taggedSyncedCache()->set('abc', 1);
        $this->taggedSyncedCache()->increment('abc', 10);

        $this->assertEquals(
            11, $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            11, $this->symfonyCache()->get('abc')
        );
    }

    public function test_tagged_decrement_sets_unset_value_to_decrement()
    {
        $this->taggedSyncedCache()->decrement('abc', 10);

        $this->assertEquals(
            -10, $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            -10, $this->symfonyCache()->get('abc')
        );
    }

    public function test_tagged_decrement_increments_existing_value_to_decrement()
    {
        $this->taggedSyncedCache()->set('abc', 1);
        $this->taggedSyncedCache()->decrement('abc', 10);

        $this->assertEquals(
            -9, $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            -9, $this->symfonyCache()->get('abc')
        );
    }

    /*
     * forever()
     */
    public function test_tagged_forever_adds_value()
    {
        $this->taggedSyncedCache()->forever('abc', 'abc');

        $this->assertEquals(
            'abc', $this->taggedLaravelCache()->get('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            'abc', $this->symfonyCache()->get('abc')
        );
    }

    /*
     * remember()
     */
    public function test_tagged_remember_stores_value_when_ttl_is_positive()
    {
        $this->taggedSyncedCache()->remember('abc', 60, fn() => 'abc');

        $this->assertEquals(
            'abc', $this->taggedLaravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );

        // Symfony should still have the value even untagged
        $this->assertEquals(
            'abc', $this->symfonyCache()->get('abc', 'default')
        );
    }

    public function test_tagged_remember_does_not_store_value_when_ttl_is_zero()
    {
        $this->taggedSyncedCache()->remember('abc', 0, fn() => 'abc');

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );

        // Symfony should also not have the value
        $this->assertEquals(
            'default', $this->symfonyCache()->get('abc', 'default')
        );
    }

    public function test_tagged_remember_does_not_store_value_when_ttl_is_negative()
    {
        $this->taggedSyncedCache()->remember('abc', -1, fn() => 'abc');

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );

        // Symfony should also not have the value
        $this->assertEquals(
            'default', $this->symfonyCache()->get('abc', 'default')
        );
    }

    /*
     * rememberForever()/sear()
     */
    /**
     * @dataProvider rememberForeverAndSearProvider
     */
    public function test_tagged_remember_forever_stores_value($rememberMethod)
    {
        $this->taggedSyncedCache()->{$rememberMethod}('abc', fn() => 'abc');

        $this->assertEquals(
            'abc', $this->taggedLaravelCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );

        // Symfony should have the value even untagged
        $this->assertEquals(
            'abc', $this->symfonyCache()->get('abc', 'default')
        );
    }

    /**
     * @dataProvider rememberForeverAndSearProvider
     */
    public function test_tagged_remember_forever_returns_existing_value_without_executing_closure($rememberMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $getterExecuted = false;
        $getter = function () use (&$getterExecuted) {
            $getterExecuted = true;
            return 'abc';
        };

        $result = $this->taggedSyncedCache()->{$rememberMethod}('abc', $getter);

        $this->assertEquals(
            'abc', $result[0]
        );

        $this->assertEquals(
            ...$result
        );

        $this->assertFalse($getterExecuted);

        // Reset
        $getterExecuted = false;

        // Symfony should have the value even untagged
        $this->assertEquals(
            'abc', $this->symfonyCache()->{$rememberMethod}('abc', $getter)
        );
    }

    /*
     * delete()
     */
    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_tagged_delete_does_nothing_for_unset_key($deleteMethod)
    {
        $this->taggedSyncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should be the same
        $this->assertNull(
            $this->symfonyCache()->get('abc')
        );
    }

    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_tagged_delete_forgets_set_key_without_expiry($deleteMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc', null);

        $this->taggedSyncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should be the same
        $this->assertNull(
            $this->symfonyCache()->get('abc')
        );
    }

    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_tagged_delete_forgets_set_key_before_expiry($deleteMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc', 60);

        $this->taggedSyncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should be the same
        $this->assertNull(
            $this->symfonyCache()->get('abc')
        );
    }

    /**
     * @dataProvider deleteAndForgetProvider
     */
    public function test_tagged_delete_forgets_set_key_after_expiry($deleteMethod)
    {
        $interval = CarbonInterval::microsecond();

        $this->taggedSyncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->taggedSyncedCache()->{$deleteMethod}('abc');

        $this->assertNull($this->taggedLaravelCache()->get('abc'));

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc')
        );

        // Symfony should be the same
        $this->assertNull(
            $this->symfonyCache()->get('abc')
        );
    }

    /*
     * getMultiple()/setMultiple()
     */
    public function test_tagged_get_multiple_returns_default_when_all_unset()
    {
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );

    }

    public function test_tagged_get_multiple_returns_default_for_one_unset()
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_tagged_get_multiple_returns_all_set_values($setMultipleMethod)
    {
        $this->markTestSkippedWhen(
            $setMultipleMethod === 'put',
            'put() isn\'t implemented the same with Redis'
        );

        $this->taggedSyncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], null);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_get_multiple_returns_only_unexpired_values($setMethod)
    {
        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', 60);
        $this->taggedSyncedCache()->{$setMethod}('xyz', 'xyz', -1);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'])
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_tagged_set_multiple_sets_expiry_for_all_values($setMultipleMethod)
    {
        $this->markTestSkippedWhen(
            $setMultipleMethod === 'put',
            'put() isn\'t implemented the same with Redis'
        );

        $this->taggedSyncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 60);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_tagged_set_multiple_with_zero_expiry_returns_defaults($setMultipleMethod)
    {
        $this->markTestSkippedWhen(
            $setMultipleMethod === 'put',
            'put() isn\'t implemented the same with Redis'
        );

        $this->taggedSyncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], 0);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_tagged_set_multiple_with_negative_expiry_returns_defaults($setMultipleMethod)
    {
        $this->markTestSkippedWhen(
            $setMultipleMethod === 'put',
            'forever() doesn\'t take an array'
        );

        $this->taggedSyncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], -1);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /*
     * many()
     */
    public function test_tagged_many_returns_null_when_all_unset_and_no_defaults_in_keys()
    {
        $this->assertEquals(
            ['abc' => null, 'xyz' => null],
            $this->taggedLaravelCache()->many(['abc', 'xyz'])
        );
        $this->assertEquals(
            ['abc' => null, 'xyz' => null],
            $this->symfonyCache()->many(['abc', 'xyz'])
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->many(['abc', 'xyz'])
        );
    }

    public function test_tagged_many_returns_default_when_all_unset_and_defaults_in_keys()
    {
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    public function test_tagged_many_returns_default_for_one_unset_when_default_set()
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->taggedLaravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->symfonyCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    /**
     * @dataProvider setMultipleAndPutProvider
     */
    public function test_tagged_many_returns_all_set_values($setMultipleMethod)
    {
        $this->markTestSkippedWhen(
            $setMultipleMethod === 'put',
            'put() isn\'t implemented the same with Redis'
        );

        $this->taggedSyncedCache()->{$setMultipleMethod}([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ], null);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->taggedLaravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'xyz'],
            $this->symfonyCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    /**
     * @dataProvider setAndPutProvider
     */
    public function test_tagged_many_returns_only_unexpired_values($setMethod)
    {
        $this->taggedSyncedCache()->{$setMethod}('abc', 'abc', 60);
        $this->taggedSyncedCache()->{$setMethod}('xyz', 'xyz', -1);

        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->taggedLaravelCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
        $this->assertEquals(
            ['abc' => 'abc', 'xyz' => 'default'],
            $this->symfonyCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->many(['abc' => 'default', 'xyz' => 'default'])
        );
    }

    /*
     * deleteMultiple()
     */
    public function test_tagged_delete_multiple_does_nothing_for_unset_data()
    {
        $this->taggedSyncedCache()->deleteMultiple(['abc', 'xyz']);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    public function test_tagged_delete_multiple_unsets_set_data()
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $this->taggedSyncedCache()->deleteMultiple(['abc', 'xyz']);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    public function test_tagged_delete_multiple_unsets_all_set_data()
    {
        $this->taggedSyncedCache()->setMultiple([
            'abc' => 'abc',
            'xyz' => 'xyz'
        ]);

        $this->taggedSyncedCache()->deleteMultiple(['abc', 'xyz']);

        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->taggedLaravelCache()->getMultiple(['abc', 'xyz'], 'default')
        );
        $this->assertEquals(
            ['abc' => 'default', 'xyz' => 'default'],
            $this->symfonyCache()->getMultiple(['abc', 'xyz'], 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->getMultiple(['abc', 'xyz'], 'default')
        );
    }

    /*
     * has()/missing()
     */
    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_tagged_has_returns_false_for_unset_key($hasMethod)
    {
        $this->assertEquals(
            $hasMethod !== 'has',
            $this->taggedLaravelCache()->{$hasMethod}('abc')
        );
        $this->assertEquals(
            $hasMethod !== 'has',
            $this->symfonyCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_tagged_has_returns_true_for_value_without_expiry($hasMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $this->assertEquals(
            $hasMethod === 'has',
            $this->taggedLaravelCache()->{$hasMethod}('abc')
        );
        $this->assertEquals(
            $hasMethod === 'has',
            $this->symfonyCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_tagged_has_returns_true_for_before_expiry($hasMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            $hasMethod === 'has',
            $this->taggedLaravelCache()->{$hasMethod}('abc')
        );
        $this->assertEquals(
            $hasMethod === 'has',
            $this->symfonyCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_tagged_has_returns_false_for_zero_expiry($hasMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc', 0);

        $this->assertEquals(
            $hasMethod !== 'has',
            $this->taggedLaravelCache()->{$hasMethod}('abc')
        );
        $this->assertEquals(
            $hasMethod !== 'has',
            $this->symfonyCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->{$hasMethod}('abc')
        );
    }

    /**
     * @dataProvider hasAndMissingProvider
     */
    public function test_tagged_has_returns_false_for_negative_expiry($hasMethod)
    {
        $this->taggedSyncedCache()->set('abc', 'abc', -1);

        $this->assertEquals(
            $hasMethod !== 'has',
            $this->taggedLaravelCache()->{$hasMethod}('abc')
        );
        $this->assertEquals(
            $hasMethod !== 'has',
            $this->symfonyCache()->{$hasMethod}('abc')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->{$hasMethod}('abc')
        );
    }

    /*
     * pull()
     */
    public function test_tagged_pull_returns_default_for_unset_key()
    {
        $this->assertEquals(
            'default', $this->taggedLaravelCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            'default', $this->symfonyCache()->pull('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->pull('abc', 'default')
        );
    }

    public function test_tagged_pull_returns_and_deletes_before_expiry()
    {
        $this->taggedSyncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            'abc', $this->taggedLaravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->taggedLaravelCache()->has('abc')
        );

        $this->taggedSyncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            'abc', $this->symfonyCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->symfonyCache()->has('abc')
        );

        // Reset and test that values are equal
        $this->taggedSyncedCache()->set('abc', 'abc', 60);

        $this->assertEquals(
            ...$this->taggedSyncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->taggedSyncedCache()->has('abc')
        );
    }

    public function test_tagged_pull_returns_default_after_expiry()
    {
        $interval = CarbonInterval::millisecond();

        $this->taggedSyncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->taggedLaravelCache()->has('abc')
        );

        $this->taggedSyncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->assertEquals(
            'default', $this->symfonyCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->symfonyCache()->has('abc')
        );

        // Reset and test that values are equal
        $interval = CarbonInterval::millisecond();

        $this->taggedSyncedCache()->set('abc', 'abc', $interval);

        usleep(10);

        $this->assertEquals(
            ...$this->taggedSyncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->taggedSyncedCache()->has('abc')
        );
    }

    public function test_tagged_pull_returns_default_with_zero_expiry()
    {
        $this->taggedSyncedCache()->set('abc', 'abc', 0);

        usleep(10);

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->taggedLaravelCache()->has('abc')
        );

        $this->taggedSyncedCache()->set('abc', 'abc', 0);

        usleep(10);

        $this->assertEquals(
            'default', $this->symfonyCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->symfonyCache()->has('abc')
        );

        // Reset and test that values are equal
        $this->taggedSyncedCache()->set('abc', 'abc', 0);

        usleep(10);

        $this->assertEquals(
            ...$this->taggedSyncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->taggedSyncedCache()->has('abc')
        );
    }

    public function test_tagged_pull_returns_default_with_negative_expiry()
    {
        $this->taggedSyncedCache()->set('abc', 'abc', -1);

        usleep(10);

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->taggedLaravelCache()->has('abc')
        );

        $this->taggedSyncedCache()->set('abc', 'abc', -1);

        usleep(10);

        $this->assertEquals(
            'default', $this->symfonyCache()->pull('abc', 'default')
        );
        $this->assertFalse(
            $this->symfonyCache()->has('abc')
        );

        // Reset and test that values are equal
        $this->taggedSyncedCache()->set('abc', 'abc', -1);

        usleep(10);

        $this->assertEquals(
            ...$this->taggedSyncedCache()->pull('abc', 'default')
        );
        $this->assertEquals(
            ...$this->taggedSyncedCache()->has('abc')
        );
    }

    /*
     * clear()/flush()
     */
    public function test_tagged_clear_clears_all_existing_values()
    {
        $this->taggedSyncedCache()->set('abc', 'abc');

        $this->taggedSyncedCache()->clear();

        $this->assertEquals(
            'default', $this->taggedLaravelCache()->get('abc', 'default')
        );
        $this->assertEquals(
            'default', $this->symfonyCache()->get('abc', 'default')
        );

        $this->assertEquals(
            ...$this->taggedSyncedCache()->get('abc', 'default')
        );
    }

    public function test_tagged_flush_clears_all_tagged_entries()
    {
        $this->taggedSymfonyCache()->set('abc', 'abc');
        $this->taggedSymfonyCache()->set('xyz', 'xyz');

        $this->taggedSymfonyCache()->flush();

        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('abc', 'default')
        );
        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('xyz', 'default')
        );
    }

    public function test_tagged_flush_only_clears_entries_with_tags_scoped_to_repository_instance()
    {
        $this->taggedSymfonyCache(['tag1'])->set('abc', 'abc');
        $this->taggedSymfonyCache(['tag2'])->set('xyz', 'xyz');

        $this->taggedSymfonyCache(['tag1'])->flush();

        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('abc', 'default')
        );
        $this->assertEquals(
            'xyz', $this->taggedSymfonyCache()->get('xyz', 'default')
        );
    }

    public function test_tagged_flush_clears_all_entries_with_tag_on_the_repository_instance()
    {
        $this->taggedSymfonyCache(['tag1'])->set('abc', 'abc');
        $this->taggedSymfonyCache(['tag1', 'tag2'])->set('xyz', 'xyz');

        /*
         * This behavior differs from Laravel's, where Laravel tags are more of a hierarchy rather than
         * tags. This is the intended behavior from Symfony.
         */
        $this->taggedSymfonyCache(['tag1'])->flush();

        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('abc', 'default')
        );
        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('xyz', 'default')
        );
    }

    public function test_tagged_flush_clears_all_entries_with_either_tag_on_the_repository_instance()
    {
        $this->taggedSymfonyCache(['tag1'])->set('abc', 'abc');
        $this->taggedSymfonyCache(['tag2'])->set('xyz', 'xyz');

        /*
         * This behavior differs from Laravel's, where Laravel tags are more of a hierarchy rather than
         * tags. This is the intended behavior from Symfony.
         */
        $this->taggedSymfonyCache(['tag1', 'tag2'])->flush();

        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('abc', 'default')
        );
        $this->assertEquals(
            'default', $this->taggedSymfonyCache()->get('xyz', 'default')
        );
    }
}
