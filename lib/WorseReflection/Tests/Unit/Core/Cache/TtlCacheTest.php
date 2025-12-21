<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Cache;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class TtlCacheTest extends TestCase
{
    public function testPutsCacheIfNotSet(): void
    {
        $cache = new TtlCache();
        self::assertNull($cache->get('foobar'));
        self::assertEquals(1234, $cache->getOrSet('foobar', function () {
            return 1234;
        }));
        self::assertNotNull($cache->get('foobar'));
    }

    public function testGetExpire(): void
    {
        // 0.5ms
        $cache = new TtlCache(0.0005);
        $count = 0;

        // cache should expire on every other iteration
        for ($i = 0; $i < 10; $i++) {
            if (null === $cache->get('foobar')) {
                $cache->set('foobar', ++$count);
            }

            // 0.25 milliseconds
            usleep(250);
        }

        self::assertGreaterThan(4, $count);
        self::assertLessThanOrEqual(6, $count);
    }

    public function testRemove(): void
    {
        $cache = new TtlCache(1);
        $count = 0;

        $cache->set('foobar', 'hello');
        self::assertNotNull($cache->get('foobar'));
        $cache->remove('foobar');
        self::assertNull($cache->get('foobar'));
    }

    public function testCallbackIsOnlyCalledOnce(): void
    {
        $cache = new TtlCache();
        $count = 0;
        for ($i = 0; $i < 5; $i++) {
            $cache->getOrSet('foobar', function () use (&$count) {
                $count++;
                return 1234;
            });
        }
        self::assertEquals(1, $count);
    }

    public function testDiscardsEntryIfExpired(): void
    {
        $cache = new TtlCache(0.0001);
        $count = 0;

        for ($i = 0; $i < 5; $i++) {
            $cache->getOrSet('foobar', function () use (&$count) {
                $count++;
                return 1234;
            });
            usleep(50);
        }

        self::assertLessThanOrEqual(5, $count);
    }
}
