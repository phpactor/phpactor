<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\LanguageServerProtocol\Range;
use Phpactor\TextDocument\ByteOffsetRange;

class RangeConverter
{
    public static function toLspRange(ByteOffsetRange $range, string $text): Range
    {
        return new Range(
            PositionConverter::byteOffsetToPosition($range->start(), $text),
            PositionConverter::byteOffsetToPosition($range->end(), $text),
        );
    }
}
