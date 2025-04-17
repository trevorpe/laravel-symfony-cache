<?php

namespace Tests\Feature\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockProvider;
use Tests\TestCase;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheFactory;

abstract class CacheLockTestCase extends TestCase
{
    protected SymfonyCacheFactory $factory;

    protected Repository $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = app(SymfonyCacheFactory::class);

        $this->cacheRepository()->clear();
    }

    abstract protected function cacheRepository(): Repository;
    protected function cacheStore(): LockProvider
    {
        return $this->cacheRepository()->getStore();
    }

    public function test_can_acquire_lock()
    {
        $lock = $this->cacheStore()->lock('test', 1);

        $this->assertTrue($lock->get());
    }

    public function test_cannot_acquire_lock_if_already_acquired()
    {
        $lock = $this->cacheStore()->lock('test', 1);

        $lock->get();

        $this->assertFalse($lock->get());
    }

    public function test_can_release_lock()
    {
        $lock = $this->cacheStore()->lock('test', 1);

        $lock->get();

        $this->assertTrue($lock->release());
    }

    public function test_can_acquire_lock_after_it_is_released()
    {
        $lock = $this->cacheStore()->lock('test', 1);

        $lock->get();
        $lock->release();

        $this->assertTrue($lock->get());
    }

    public function test_can_acquire_lock_with_owner()
    {
        $lock = $this->cacheStore()->lock('test', 1);

        $lock->get();

        $restoredLock = $this->cacheStore()->restoreLock('test', $lock->owner());

        $this->assertTrue($restoredLock->release());
    }

    public function test_can_acquire_lock_with_custom_owner()
    {
        $lock = $this->cacheStore()->lock('test', 1, 'owner');

        $lock->get();

        $restoredLock = $this->cacheStore()->restoreLock('test', 'owner');

        $this->assertTrue($restoredLock->release());
    }
}
