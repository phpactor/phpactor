<?php

namespace Phpactor\Name;

final class NameUtil
{
    public static function relativeTo(string $search, string $fqn): string
    {
        $fqn = explode('\\', $fqn);
        $search = explode('\\', $search);
        $rel = [];

        foreach ($fqn as $segment) {
            $seg = array_shift($search);
            if ($seg === $segment) {
                continue;
            }

            $rel[] = $segment;
        }
        return implode($rel);
    }
}
