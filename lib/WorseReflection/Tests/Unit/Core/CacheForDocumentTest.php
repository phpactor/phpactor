<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\StaticCache;
use Psr\Log\NullLogger;
use stdClass;

class CacheForDocumentTest extends TestCase
{
    public function testGetSet(): void
    {
        $cache = new CacheForDocument(fn () => new StaticCache());
        $result = $cache->getOrSet(TextDocumentUri::fromString('file:///foo'), 'bar', function () {
            return 'bar';
        });
        self::assertEquals('bar', $result);
        $result = $cache->getOrSet(TextDocumentUri::fromString('file:///foo'), 'bar', function () {
            return 'boo';
        });
        self::assertEquals('bar', $result);
        $result = $cache->getOrSet(TextDocumentUri::fromString('file:///baz'), 'bar', function () {
            return 'boo';
        });
        self::assertEquals('boo', $result);
    }

    public function testPurge(): void
    {
        $uri = TextDocumentUri::fromString('file:///baz');

        $cache = new CacheForDocument(
            fn () => new StaticCache(),
            new NullLogger(),
            purgeGracePeriodSeconds: 0.1,
        );
        $result1 = $cache->getOrSet($uri, 'bar', function () {
            return new stdClass();
        });
        $result2 = $cache->getOrSet($uri, 'bar', function () {
            return new stdClass();
        });
        self::assertSame($result1, $result2);

        $result1 = $cache->getOrSet($uri, 'bar', function () {
            return new stdClass();
        });
        $cache->purge($uri);
        $result2 = $cache->getOrSet($uri, 'bar', function () {
            return new stdClass();
        });

        // we do not purge it instantaenously
        self::assertSame($result1, $result2);

        // sleep for 0.1 seconds
        usleep(100_100);

        $result3 = $cache->getOrSet($uri, 'bar', function () {
            return new stdClass();
        });
        self::assertNotSame($result1, $result3);
    }
}
