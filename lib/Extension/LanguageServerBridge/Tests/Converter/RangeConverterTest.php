<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Converter;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;

class RangeConverterTest extends TestCase
{
    public function testConvertsRanges(): void
    {
        $text = '1234567890';
        $start = ByteOffset::fromInt(1);
        $end = ByteOffset::fromInt(4);
        self::assertEquals(
            new Range(
                PositionConverter::byteOffsetToPosition($start, $text),
                PositionConverter::byteOffsetToPosition($end, $text),
            ),
            RangeConverter::toLspRange(
                ByteOffsetRange::fromByteOffsets($start, $end),
                $text
            )
        );
    }
}
