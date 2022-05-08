<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\InterfaceBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CompletionContext
{
    public static function expression(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }
        $parent = $node->parent;

        if (null === $parent) {
            return false;
        }

        if (self::classMembersBody($node)) {
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

    public static function useImport(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }
        return $node->parent instanceof NamespaceUseClause;
    }

    public static function classLike(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }
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

    public static function type(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }

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

    public static function classMembersBody(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }

        if ($node instanceof ClassMembersNode) {
            return true;
        }

        if (null === $node->parent) {
            return false;
        }

        $nodeBeforeOffset = NodeUtil::firstDescendantNodeBeforeOffset($node->getRoot(), $node->parent->getStartPosition());

        if ($node instanceof Variable) {
            return false;
        }

        if ($nodeBeforeOffset instanceof ClassMembersNode) {
            return true;
        }

        if (!$nodeBeforeOffset->getFirstAncestor(ClassLike::class)) {
            return false;
        }

        if ($nodeBeforeOffset instanceof CompoundStatementNode && $node->getStartPosition() < $nodeBeforeOffset->getEndPosition()) {
            return false;
        }

        $methodDeclaration = $nodeBeforeOffset->getFirstAncestor(MethodDeclaration::class);

        if (!$methodDeclaration) {
            return true;
        }

        if ($methodDeclaration->getEndPosition() < $node->getStartPosition()) {
            return true;
        }

        return false;
    }

    public static function classClause(?Node $node, ByteOffset $offset): bool
    {
        if (null === $node) {
            return false;
        }

        $prefix = substr($node->getFileContents(), 0, $offset->toInt());
        if (preg_match('{(class|interface|trait)\s+[^\s]+\s*[^\s\{]*$}', $prefix)) {
            return true;
        }
        if (preg_match('{(class|interface|trait)\s+[^\s]+\s*(implements|extends)\s+([^,\s\{]+[,\s]*)*$}', $prefix)) {
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
            $node instanceof InterfaceBaseClause ||
            $node instanceof ClassInterfaceClause ||
            $node instanceof TraitUseClause ||
            $node instanceof ClassBaseClause;
    }
}
