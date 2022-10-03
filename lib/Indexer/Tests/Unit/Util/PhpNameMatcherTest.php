<?php

namespace Phpactor\Indexer\Tests\Unit\Util;

use Phpactor\Indexer\Util\PhpNameMatcher;
use PHPUnit\Framework\TestCase;

class PhpNameMatcherTest extends TestCase
{
    public function testMatch(): void
    {
        self::assertTrue(PhpNameMatcher::isPhpName('Foobar'));
        self::assertTrue(PhpNameMatcher::isPhpName('foobar'));
        self::assertFalse(PhpNameMatcher::isPhpName('$foobar'));
    }
}
