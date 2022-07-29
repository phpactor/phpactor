<?php

namespace Phpactor\Indexer\Util;

class PhpNameMatcher
{
    public static function isClassName(string $name): bool
    {
        // https://www.php.net/manual/en/language.oop5.basic.php
        return (bool)preg_match('{^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$}', $name);
    }
}
