<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\LanguageServerProtocol\Position;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\Util\LineAtOffset;

class PositionConverter
{
    public static function intByteOffsetToPosition(int $offset, string $text): Position
    {
        return self::byteOffsetToPosition(ByteOffset::fromInt($offset), $text);
    }

    public static function byteOffsetToPosition(ByteOffset $offset, string $text): Position
    {
        if ($offset->toInt() > strlen($text)) {
            $offset = ByteOffset::fromInt(strlen($text));
        }

        $lineCol = LineCol::fromByteOffset($text, $offset);
        $lineAtOffset = LineAtOffset::lineAtByteOffset($text, $offset);

        $lineAtOffset = self::normalizeUTF16(mb_substr(
            $lineAtOffset,
            0,
            $lineCol->col() - 1
        ));

        return new Position($lineCol->line() - 1, strlen($lineAtOffset) / 2);
    }
    
    private static function normalizeUTF16(string $string): string
    {
        $utf16 = \mb_convert_encoding($string, 'UTF-16', 'UTF-8');
        if ($utf16 === false) {
            throw new RuntimeException('String cannot be converted to UTF-16');
        }

        return $utf16;
    }

    public static function positionToByteOffset(Position $position, string $text): ByteOffset
    {
        $lineCol = new LineCol($position->line + 1, 1);
        $byteOffset = $lineCol->toByteOffset($text);

        return ByteOffset::fromInt($byteOffset->toInt() + $position->character);
    }
}
