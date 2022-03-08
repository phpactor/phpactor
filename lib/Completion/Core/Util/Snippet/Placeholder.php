<?php

namespace Phpactor\Completion\Core\Util\Snippet;

final class Placeholder
{
    public static function raw(int $position, ?string $default = null): string
    {
        return \sprintf('${%d%s}', $position, $default ? ":$default" : null);
    }

    public static function escape(int $position, ?string $default = null): string
    {
        return self::raw($position, self::protect($default));
    }

    private static function protect(?string $text = null): string
    {
        if (null === $text) {
            return '';
        }
        // Important to escape the backslash first!
        return str_replace(['\\', '$', '}'], ['\\\\', '\$', '\}'], $text);
    }
}
