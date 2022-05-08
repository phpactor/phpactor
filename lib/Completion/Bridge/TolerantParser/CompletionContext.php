<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;

class CompletionContext
{
    public static function expression(Node $node): bool
    {
        $parent = $node->parent;
        return
            $parent instanceof Expression ||
            $parent instanceof ExpressionStatement ||
            $parent instanceof IfStatementNode ||
            $parent instanceof ArrayElement // yield;
        ;
    }

    public static function useImport(Node $node): bool
    {
        return $node->parent instanceof NamespaceUseClause;
    }

    public static function classLike(Node $node): bool
    {
        $parent = $node->parent;
        if ($parent->parent) {
            if (
                $parent->parent instanceof ClassInterfaceClause ||
                $parent->parent instanceof ClassBaseClause
            ) {
                return true;
            }
        }
        return
            $parent instanceof ClassInterfaceClause ||
            $parent instanceof ClassBaseClause
        ;

    }

    public static function type(Node $node): bool
    {
        if (
            $node->parent instanceof Parameter ||
            $node->parent instanceof QualifiedNameList
        ) {
            return true;
        }

        return false;
    }
}
