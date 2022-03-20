<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Cache;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class TtlCacheTest extends TestCase
{
    public function testPutsCacheIfNotSet(): void
    {
        $cache = new TtlCache();
        self::assertEquals(1234, $cache->getOrSet('foobar', function () {
            return 1234;
        }));
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
