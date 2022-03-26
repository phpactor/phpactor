<?php

namespace Phpactor\WorseReflection\Core\Util;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;

class NodeUtil
{
    /**
     * @param Token|QualifiedName|mixed $name
     */
    public static function nameFromTokenOrQualifiedName(Node $node, $name): string
    {
        if ($name instanceof Token) {
            return (string)$name->getText($node->getFileContents());
        }
        if ($name instanceof QualifiedName) {
            return $name->__toString();
        }

        return '';
    }

    public static function qualifiedNameListContains(?QualifiedNameList $list, string $name): bool
    {
        if (null === $list) {
            return false;
        }
        foreach ($list->getElements() as $element) {
            if (!$element instanceof QualifiedName) {
                continue;
            }
            if ((string)$element->getResolvedName() === $name) {
                return true;
            }
        }

        return false;
    }

    public static function qualfiiedNameIs(?QualifiedName $qualifiedName, string $name): bool
    {
        if (null === $qualifiedName) {
            return false;
        }

        return (string)$qualifiedName->getResolvedName() === $name;
    }
}
