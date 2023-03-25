<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffset;

class OffsetTest extends TestCase
{
    const OFFSET = 123;

    public function testFromPhpactorByteOffset(): void
    {
        $byteOffset = ByteOffset::fromInt(self::OFFSET);
        $offset = ByteOffset::fromUnknown($byteOffset);

        $this->assertSame(self::OFFSET, $offset->toInt());
    }

    public function testFromUnknownReturnsOffsetIfGivenOffset(): void
    {
        $givenOffset = ByteOffset::fromInt(self::OFFSET);
        $offset = ByteOffset::fromUnknown($givenOffset);

        $this->assertSame($givenOffset, $offset);
    }

    public function testFromUnknownString(): void
    {
        $offset = ByteOffset::fromUnknown(self::OFFSET);

        $this->assertEquals(ByteOffset::fromInt(self::OFFSET), $offset);
    }
}
