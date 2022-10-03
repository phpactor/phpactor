<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Unit\Converter;

use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\TextDocument\ByteOffset;
use PHPUnit\Framework\TestCase;

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
}
