<?php

namespace Phpactor\CodeBuilder\Util;

use RuntimeException;

class TextFormat
{
    private string $indentation;

    private string $newLineChar;

    public function __construct(string $indentation = '    ', string $newLineChar = "\n")
    {
        $this->indentation = $indentation;
        $this->newLineChar = $newLineChar;
    }

    public function indent(string $string, int $level = 0): string
    {
        if ($level < 0) {
            throw new RuntimeException(sprintf(
                'Level must be greater than or equal to 0, got "%s"',
                $level
            ));
        }
        $lines = TextUtil::lines($string);
        $lines = array_map(function ($line) use ($level) {
            return str_repeat($this->indentation, $level) . $line;
        }, $lines);

        return implode($this->newLineChar, $lines);
    }

    public function indentRemove(string $text): string
    {
        return preg_replace("/^[ \t]+/m", '', $text);
    }

    public function indentReplace($text, int $level): string
    {
        return $this->indent($this->indentRemove($text), $level);
    }
}
