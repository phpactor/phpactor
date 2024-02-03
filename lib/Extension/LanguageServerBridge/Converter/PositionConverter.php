<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\LanguageServerProtocol\Position;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\Util\LineAtOffset;
use RuntimeException;

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

        $lineAtOffset = mb_substr(
            $lineAtOffset,
            0,
            $lineCol->col() - 1
        );

        return new Position($lineCol->line() - 1, self::countUtf16CodeUnits($lineAtOffset));
    }

    public static function positionToByteOffset(Position $position, string $text): ByteOffset
    {
        $lineCol = new LineCol($position->line + 1, 1);
        $byteOffset = $lineCol->toByteOffset($text);

        return ByteOffset::fromInt($byteOffset->toInt() + $position->character);
    }

    /**
     * Stolen from: https://github.com/symfony/symfony/issues/45459#issuecomment-1045502304
     */
    private static function normalizeUtf16(string $string): string
    {
        $utf16 = \mb_convert_encoding($string, 'UTF-16', 'UTF-8');
        if (!is_string($utf16)) {
            throw new RuntimeException('String cannot be converted to UTF-16');
        }

        return $utf16;
    }

    private static function countUtf16CodeUnits(string $string): int
    {
        return (int)(strlen(self::normalizeUtf16($string)) / 2);
    }
}
