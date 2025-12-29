<?php

namespace Phpactor\Completion\Core\Util;

use RuntimeException;
use function preg_last_error_msg;

class OffsetHelper
{
    public static function trimToLastNonWhitespaceCharacter(string $input): string
    {
        $source = preg_replace('/[ \t\x0d\n\r\f]+$/u', '', $input);

        if (null === $source) {
            throw new RuntimeException(sprintf(
                'preg_replace could not parse string (size %s): %s',
                strlen($input),
                preg_last_error_msg()
            ));
        }

        return $source;
    }

    public static function lastNonWhitespaceCharacterOffset(string $input): int
    {
        return mb_strlen(self::trimToLastNonWhitespaceCharacter($input));
    }

    public static function lastNonWhitespaceByteOffset(string $input): int
    {
        return strlen(self::trimToLastNonWhitespaceCharacter($input));
    }
}
