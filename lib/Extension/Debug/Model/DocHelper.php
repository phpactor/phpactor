<?php

namespace Phpactor\Extension\Debug\Model;

class DocHelper
{
    public static function title(string $char, string $title): string
    {
        return implode("\n", [$title, str_repeat($char, mb_strlen($title))]);
    }

    public static function indent(int $level, string $text): string
    {
        return str_replace("\n", sprintf("\n%s", str_repeat(' ', $level)), $text);
    }
}
