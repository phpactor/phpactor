<?php

namespace Phpactor\CodeTransform\Domain\Utils;

class TextUtils
{
    public static function removeIndentation(string $string): string
    {
        $indentation = null;
        $lines = explode(PHP_EOL, $string);

        foreach ($lines as $i => $line) {
            if ($line === '') {
                continue;
            }

            preg_match('{^(\s+).*$}', $line, $matches);

            if (false === isset($matches[1])) {
                $indentation = 0;
                break;
            }

            $count = mb_strlen($matches[1]);

            if (null === $indentation || $count < $indentation) {
                $indentation = $count;
            }
        }

        if (null === $indentation) {
            $indentation = 0;
        }

        foreach ($lines as &$line) {
            $line = substr($line, $indentation);
        }

        return trim(implode(PHP_EOL, $lines), PHP_EOL);
    }

    public static function stringIndentation(string $string): int
    {
        $lines = explode(PHP_EOL, $string);

        if (empty($lines)) {
            return 0;
        }

        preg_match('{^(\s+).*$}m', $lines[0], $matches);

        if (false === isset($matches[1])) {
            return 0;
        }

        return mb_strlen($matches[1]);
    }
}
