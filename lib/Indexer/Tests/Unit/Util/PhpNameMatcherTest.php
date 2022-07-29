<?php

namespace Phpactor\Indexer\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Util\PhpNameMatcher;

class PhpNameMatcherTest extends TestCase
{
    public function testMatch(): void
    {
        self::assertTrue(PhpNameMatcher::isPhpName('Foobar'));
        self::assertTrue(PhpNameMatcher::isPhpName('foobar'));
        self::assertFalse(PhpNameMatcher::isPhpName('$foobar'));
    }
}
