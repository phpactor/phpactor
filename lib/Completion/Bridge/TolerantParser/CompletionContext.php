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
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\TraitUseClause;

class CompletionContext
{
    public static function expression(Node $node): bool
    {
        $parent = $node->parent;

        if (null === $parent) {
            return false;
        }

        return
            $parent instanceof Expression ||
            $parent instanceof ExpressionStatement ||
            $parent instanceof IfStatementNode ||
            $parent instanceof ReturnStatement ||
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
        if (null === $parent) {
            return false;
        }
        if ($parent->parent) {
            if (self::isClassClause($parent->parent)) {
                return true;
            }
        }

        return self::isClassClause($parent);
    }

    public static function type(Node $node): bool
    {
        if (null === $node->parent) {
            return false;
        }

        // no type-completion clauses (extends, implements, use)
        // as these are class-like only
        if ($node->parent->parent) {
            if (
                self::isClassClause($node->parent->parent)
            ) {
                return false;
            }
        }

        if (
            $node->parent instanceof Parameter ||
            $node->parent instanceof QualifiedNameList
        ) {
            return true;
        }

        return false;
    }

    public static function nodeOrParentIs(?Node $node, string $type): bool
    {
        if (null === $node) {
            return false;
        }
        if ($node instanceof $type) {
            return true;
        }

        if ($node->parent instanceof $type) {
            return true;
        }
        return false;
    }

    private static function isClassClause(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }
        return
            $node instanceof ClassInterfaceClause ||
            $node instanceof TraitUseClause ||
            $node instanceof ClassBaseClause;
    }
}
