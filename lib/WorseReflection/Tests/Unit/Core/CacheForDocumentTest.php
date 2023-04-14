<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\StaticCache;

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
}
