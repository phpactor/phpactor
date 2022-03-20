<?php

namespace Phpactor\TextDocument\Tests\Unit;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdit;

class TextEditTest extends TestCase
{
    public function testExceptionIfLengthIsNegative1(): void
    {
        $this->expectException(OutOfRangeException::class);
        TextEdit::create(10, -10, 'asd');
    }

    public function testExceptionIfLengthIsNegative2(): void
    {
        $this->expectException(OutOfRangeException::class);
        TextEdit::create(10, -1, 'asd');
    }

    public function testLengthIsZero(): void
    {
        self::assertEquals(0, TextEdit::create(10, 0, 'asd')->length());
    }

    public function testReturnLength(): void
    {
        self::assertEquals(1, TextEdit::create(10, 1, 'asd')->length());
    }

    public function testCreateWithByteOffset(): void
    {
        self::assertEquals(1, TextEdit::create(ByteOffset::fromInt(10), 1, 'asd')->length());
    }
}
