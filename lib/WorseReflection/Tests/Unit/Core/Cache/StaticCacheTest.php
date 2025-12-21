<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Cache;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Cache\StaticCache;

class StaticCacheTest extends TestCase
{
    public function testGetOrSet(): void
    {
        $cache = new StaticCache();
        $counter = 0;
        $value = $cache->getOrSet('foobar', fn () => $counter++);
        self::assertEquals(0, $counter);
        $value = $cache->getOrSet('foobar', fn () => $counter++);
        self::assertEquals(0, $counter);
    }

    public function testGetHasSet(): void
    {
        $cache = new StaticCache();
        $counter = 0;

        self::assertFalse($cache->has('foo'));

        $cache->set('foo', 'bar');

        self::assertTrue($cache->has('foo'));
        self::assertEquals('bar', $cache->get('foo'));

        $cache->remove('foo');

        self::assertFalse($cache->has('foo'));
    }
}
