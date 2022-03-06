<?php

namespace Phpactor\CodeBuilder\Util;

final class TextUtil
{
    public static function lines(string $text): array
    {
        return preg_split("{(\r\n|\n|\r)}", $text);
    }
}
