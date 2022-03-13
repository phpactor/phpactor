<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;

class NodeHelper
{
    /**
     * @param QualifiedName|Token $type
     */
    public static function resolvedShortName(Node $node, $type = null): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof Token) {
            return $type->getText($node->getFileContents());
        }

        $resolvedName = $type->getResolvedName();

        if (is_string($resolvedName)) {
            return $resolvedName;
        }

        $parts = $resolvedName->getNameParts();

        if (count($parts) === 0) {
            return '';
        }

        $part = '';

        if (count($parts) == 1) {
            $part = reset($parts);
        }

        if (count($parts) > 1) {
            $part = array_pop($parts);
        }

        if ($part instanceof Token) {
            return $part->getText($type->getFileContents());
        }

        return $part;
    }

    public static function emptyLinesPrecedingNode(Node $node): int
    {
        $contents = $node->getFileContents();
        $preceding = substr($contents, 0, $node->getStartPosition());

        $lines = 0;
        $lastChar = null;
        for ($i = $node->getStartPosition() - 1; $i > 0; $i--) {
            $char = $contents[$i];

            if ($char !== "\n") {
                break;
            }

            $lines++;
        }

        return $lines - 1;
    }
}
