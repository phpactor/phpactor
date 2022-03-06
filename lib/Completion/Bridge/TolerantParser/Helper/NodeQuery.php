<?php

namespace Phpactor\Completion\Bridge\TolerantParser\Helper;

use Microsoft\PhpParser\Node;

class NodeQuery
{
    /**
     * @template C of Node
     * @param class-string<C> $className
     * @param list<class-string> $validDescendants
     * @return C|null
     */
    public static function firstAncestorVia(Node $node, string $className, array $validDescendants): ?Node
    {
        $ancestor = $node;
        while ($ancestor = $ancestor->parent) {
            if ($ancestor instanceof $className) {
                return $ancestor;
            }

            if (!in_array(get_class($ancestor), $validDescendants)) {
                break;
            }
        }

        return null;
    }

    /**
     * @template C of Node
     * @param list<class-string<C>> $classNames
     * @param list<class-string> $validDescendants
     * @return ?C
     */
    public static function firstAncestorInVia(Node $node, array $classNames, array $validDescendants): ?Node
    {
        $ancestor = $node;

        while ($ancestor = $ancestor->parent) {
            if (in_array(get_class($ancestor), $classNames)) {
                /** @phpstan-ignore-next-line */
                return $ancestor;
            }

            if (!in_array(get_class($ancestor), $validDescendants)) {
                break;
            }
        }

        return null;
    }

    /**
     * @template C of Node
     * @param list<class-string<C>> $classNames
     * @param list<class-string> $validDescendants
     * @return ?C
     */
    public static function firstAncestorOrSelfInVia(Node $node, array $classNames, array $validDescendants): ?Node
    {
        if (in_array(get_class($node), $classNames)) {
            /** @phpstan-ignore-next-line */
            return $node;
        }

        return self::firstAncestorInVia($node, $classNames, $validDescendants);
    }
}
