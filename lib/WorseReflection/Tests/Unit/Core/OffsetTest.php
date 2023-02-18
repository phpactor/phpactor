<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\WorseReflection\Core\Offset;

class OffsetTest extends TestCase
{
    const OFFSET = 123;

    public function testFromPhpactorByteOffset(): void
    {
        $byteOffset = ByteOffset::fromInt(self::OFFSET);
        $offset = Offset::fromUnknown($byteOffset);

        $this->assertSame(self::OFFSET, $offset->toInt());
    }

    public function testFromUnknownReturnsOffsetIfGivenOffset(): void
    {
        $givenOffset = Offset::fromInt(self::OFFSET);
        $offset = Offset::fromUnknown($givenOffset);

        $this->assertSame($givenOffset, $offset);
    }

    public function testFromUnknownString(): void
    {
        $offset = Offset::fromUnknown(self::OFFSET);

        $this->assertEquals(Offset::fromInt(self::OFFSET), $offset);
    }
}
