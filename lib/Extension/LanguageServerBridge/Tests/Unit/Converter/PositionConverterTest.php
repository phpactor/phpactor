<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Unit\Converter;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\TextDocument\ByteOffset;

class PositionConverterTest extends TestCase
{
    public function testWhenOutOfBoundsAssumeEndOfDocument(): void
    {
        self::assertEquals(
            new Position(0, 10),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(20),
                '0123456789'
            )
        );
    }

    public function testUtf16(): void
    {
        self::assertEquals(
            new Position(0, 0),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(0),
                'a𐐀b'
            )
        );

        self::assertEquals(
            new Position(0, 1),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(1),
                'a𐐀b'
            )
        );
        self::assertEquals(
            new Position(0, 3),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(2),
                'a𐐀b'
            )
        );
    }
}
