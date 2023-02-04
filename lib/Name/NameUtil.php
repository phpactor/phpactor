<?php

namespace Phpactor\Name;

final class NameUtil
{
    /**
     * Return the FQN relative to the search.
     *
     * @param string $search - an absolute (but probably incomplete) qualified name
     * @param string $fqn - a fully qualfiied name
     *
     * @return string the name relative to the last namespace of the search.
     */
    public static function relativeToSearch(string $search, string $fqn): string
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
        return implode('\\', $rel);
    }

    public static function isQualified(string $name): bool
    {
        return str_contains($name, '\\');
    }

    /**
     * Return the child segment name relative to the given search.
     *
     * @return array{?string,bool}
     */
    public static function childSegmentAtSearch(string $fqn, string $search): array
    {
        $fqn = explode('\\', $fqn);
        $search = explode('\\', $search);
        $rel = [];

        foreach ($fqn as $index => $segment) {
            $seg = array_shift($search);
            if ($segment === $seg) {
                continue;
            }

            return [$segment, $index === count($fqn) - 1];
        }

        return [null, false];
    }
}
