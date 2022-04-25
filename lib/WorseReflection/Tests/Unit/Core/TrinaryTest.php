<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Trinary;

class TrinaryTest extends TestCase
{
    /**
     * @dataProvider provideOr
     */
    public function testOr(Trinary $trinary, Trinary $expected): void
    {
        self::assertEquals($trinary, $expected);
    }

    public function provideOr(): Generator
    {
        yield [
            Trinary::maybe()->or(fn (Trinary $t) => Trinary::true()),
            Trinary::true()
        ];
        yield [
            Trinary::true()->or(fn (Trinary $t) => Trinary::false()),
            Trinary::true()
        ];
    }
}
