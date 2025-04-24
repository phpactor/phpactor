<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\AttributeGroup;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\DelimitedList\MatchArmConditionList;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\InterfaceBaseClause;
use Microsoft\PhpParser\Node\MatchArm;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName as MicrosoftQualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\StatementNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\WhileStatement;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Microsoft\PhpParser\TokenKind;
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

        if (
            $parent instanceof BinaryExpression
                && $parent->operator->kind === TokenKind::LessThanToken
                && str_starts_with(ltrim($parent->__toString()), '<<<')
        ) {
            return false;
        }

        if ($parent instanceof ArgumentExpression) {
            return true;
        }

        if (self::classMembersBody($node)) {
            return false;
        }
        $previous = NodeUtil::previousSibling($node->parent);

        if ($previous instanceof InlineHtml) {
            $phpTag = $previous->scriptSectionStartTag?->getText($previous->getFileContents());

            if ($phpTag === '<?' && $node->getStartPosition() === $previous->getEndPosition()) {
                return false;
            }
        }

        return
            $parent instanceof Expression ||
            $parent instanceof StatementNode ||
            $parent instanceof ConstElement ||
            $parent instanceof MatchArmConditionList ||
            $parent instanceof MatchArm ||
            $parent instanceof ArrayElement // yield;
        ;
    }

    public static function attribute(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }

        return
            $node instanceof AttributeGroup ||
            $node instanceof Attribute ||
            $node->parent instanceof Attribute;
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

        if ($node->parent instanceof ConstElement) {
            return false;
        }

        if ($node instanceof Variable) {
            return false;
        }

        if (
            $node->parent instanceof MethodDeclaration
                && $node instanceof CompoundStatementNode
                && $node->openBrace instanceof MissingToken
        ) {
            return false;
        }

        $nodeBeforeOffset = NodeUtil::firstDescendantNodeBeforeOffset($node->getRoot(), $node->parent->getStartPosition());

        if ($nodeBeforeOffset instanceof ClassMembersNode) {
            return true;
        }

        $classLike = $nodeBeforeOffset->getFirstAncestor(ClassLike::class);
        if (!$classLike) {
            return false;
        }
        if ($classLike->getEndPosition() < $node->getStartPosition()) {
            if ($classLike instanceof ClassDeclaration) {
                if (!$classLike->classMembers->closeBrace instanceof MissingToken) {
                    return false;
                }
            }
            if ($classLike instanceof InterfaceDeclaration) {
                if (!$classLike->interfaceMembers->closeBrace instanceof MissingToken) {
                    return false;
                }
            }
            if ($classLike instanceof TraitDeclaration) {
                if (!$classLike->traitMembers->closeBrace instanceof MissingToken) {
                    return false;
                }
            }
            if ($classLike instanceof EnumDeclaration) {
                if (!$classLike->enumMembers->closeBrace instanceof MissingToken) {
                    return false;
                }
            }
        }

        if ($nodeBeforeOffset instanceof CompoundStatementNode && $node->getStartPosition() < $nodeBeforeOffset->getEndPosition()) {
            return false;
        }

        $memberDeclaration = $nodeBeforeOffset->getFirstAncestor(MethodDeclaration::class, ConstElement::class);

        if (!$memberDeclaration) {
            return true;
        }

        if ($memberDeclaration->getEndPosition() < $node->getStartPosition()) {
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

    public static function anonymousUse(Node $node): bool
    {
        if (!$node->parent) {
            return false;
        }
        $compound = $node->parent->parent;
        if (!$compound instanceof CompoundStatementNode) {
            return false;
        }
        $anonymous = $compound->parent;
        if (!$anonymous instanceof AnonymousFunctionCreationExpression) {
            return false;
        }
        if (!$compound->openBrace instanceof MissingToken) {
            return false;
        }
        if (!$anonymous->anonymousFunctionUseClause) {
            return false;
        }

        return true;
    }

    public static function methodName(Node $node): bool
    {
        // If the body (as the current node) is empty, the parent is MethodDeclaration
        if ($node instanceof CompoundStatementNode && !$node->openBrace instanceof MissingToken) {
            return false;
        }

        if (!$node->parent instanceof MethodDeclaration) {
            return false;
        }

        return $node->parent->openParen instanceof MissingToken;
    }

    public static function declaration(Node $node, ByteOffset $offset): bool
    {
        if (!$node->parent) {
            return false;
        }
        if (!$node->parent->parent) {
            return false;
        }
        if (!$node instanceof MicrosoftQualifiedName) {
            return false;
        }
        if (!$node->parent->parent instanceof SourceFileNode) {
            return false;
        }

        if ($node->parent->getText() !== $node->getText()) {
            return false;
        }

        $previous = NodeUtil::previousSibling($node->parent);

        // for some reason `class Foobar { func<>` will result in `func` being an sibling to `class Foobar` instead of
        // within the members node.
        // To fix this ensure that if the previous is a declaration then make sure that it doesn't have a missing closed token
        if ($previous instanceof ClassDeclaration) {
            if ($previous->classMembers->closeBrace instanceof MissingToken) {
                return false;
            }
        }
        if ($previous instanceof TraitDeclaration) {
            if ($previous->traitMembers->closeBrace instanceof MissingToken) {
                return false;
            }
        }
        if ($previous instanceof InterfaceDeclaration) {
            if ($previous->interfaceMembers->closeBrace instanceof MissingToken) {
                return false;
            }
        }
        if ($previous instanceof EnumDeclaration) {
            if ($previous->enumMembers->closeBrace instanceof MissingToken) {
                return false;
            }
        }
        if ($node->getEndPosition() < $previous->getEndPosition()) {
            return false;
        }

        return true;
    }

    public static function promotedPropertyVisibility(Node $node): bool
    {
        $methodDeclaration = $node->getFirstAncestor(MethodDeclaration::class);
        if (!$methodDeclaration instanceof MethodDeclaration) {
            return false;
        }
        if ($methodDeclaration->getName() !== '__construct') {
            return false;
        }
        if ($node instanceof CompoundStatementNode) {
            return true;
        }
        $parameter = $node->getFirstAncestor(Parameter::class);
        if (!$parameter instanceof Parameter) {
            return false;
        }
        if (NodeUtil::nullOrMissing($parameter->variableName)) {
            return true;
        }

        return false;
    }

    public static function conditionInfix(Node $node): bool
    {
        // If the current node is not a variable we're at the beginning of the condition like "if (<>"
        if ($node->getText() === '') {
            return false;
        }

        $parent = $node->parent;
        return $parent instanceof IfStatementNode || $parent instanceof WhileStatement;
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
