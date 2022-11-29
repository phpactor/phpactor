<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Util;

/**
 * An utility class to get details of shared begining and ending of a string
 */
class StringSharedChars
{
    /**
     * Counts number of shared characters on the begining of a string
     */
    public static function startLength(string $a, string $b): int
    {
        $a = str_split($a);
        $b = str_split($b);

        foreach ($a as $index => $letter) {
            if ($letter !== ($b[$index] ?? null)) {
                return $index;
            }
        }

        return count($a);
    }

    /**
     * Counts number of shared characters on the end of a string
     */
    public static function endLength(string $a, string $b): int
    {
        return self::startLength(strrev($a), strrev($b));
    }

    /**
     * Gets the position of the shared ending string between args
     */
    public static function endPos(string $a, string $b): int
    {
        $end = self::endLength($a, $b);
        $strlen = strlen($a);

        return $end === $strlen
            ? $end
            : $strlen - $end;
    }
}
