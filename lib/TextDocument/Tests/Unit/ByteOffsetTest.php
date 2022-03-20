<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Exception\InvalidByteOffset;

class ByteOffsetTest extends TestCase
{
    public function testValue(): void
    {
        $offset = ByteOffset::fromInt(10);
        $this->assertEquals(10, $offset->toInt());
    }

    public function testAdd(): void
    {
        $offset = ByteOffset::fromInt(10)->add(10);
        $this->assertEquals(20, $offset->toInt());
    }

    public function testExceptionOnLessThanZero1(): void
    {
        $this->expectException(InvalidByteOffset::class);
        ByteOffset::fromInt(-1);
    }

    public function testExceptionOnLessThanZero2(): void
    {
        $this->expectException(InvalidByteOffset::class);
        ByteOffset::fromInt(-10);
    }

    public function testByteOffsetIsZero(): void
    {
        self::assertEquals(0, ByteOffset::fromInt(0)->toInt());
    }
}
