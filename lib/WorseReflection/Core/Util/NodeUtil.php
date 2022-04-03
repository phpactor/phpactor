<?php

namespace Phpactor\WorseReflection\Core\Util;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Reflector;

class NodeUtil
{
    public static function nodeContainerClassLikeType(Reflector $reflector, Node $node): Type
    {
        $classNode = self::nodeContainerClassLikeDeclaration($node);

        if (null === $classNode) {
            return TypeFactory::undefined();
        }

        assert($classNode instanceof NamespacedNameInterface);

        return TypeFactory::fromStringWithReflector($classNode->getNamespacedName(), $reflector);
    }

    /**
     * @return ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null
     */
    public static function nodeContainerClassLikeDeclaration(Node $node): ?Node
    {
        $ancestor = $node->getFirstAncestor(ObjectCreationExpression::class, ClassLike::class);

        if ($ancestor instanceof ObjectCreationExpression) {
            if ($ancestor->classTypeDesignator instanceof Token) {
                if ($ancestor->classTypeDesignator->kind == TokenKind::ClassKeyword) {
                    throw new CouldNotResolveNode('Resolving anonymous classes is not currently supported');
                }
            }

            return self::nodeContainerClassLikeDeclaration($ancestor);
        }

        /** @var ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null */
        return $ancestor;
    }

    /**
     * @param Token|Node|mixed $nodeOrToken
     */
    public static function nameFromTokenOrNode(Node $node, $nodeOrToken): string
    {
        if ($nodeOrToken instanceof Token) {
            return (string)$nodeOrToken->getText($node->getFileContents());
        }
        if ($nodeOrToken instanceof Node) {
            return (string)$nodeOrToken->getText();
        }

        return '';
    }

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

    /**
     * @return int
     */
    public static function operatorKindForUnaryExpression(UnaryExpression $node): int
    {
        foreach ($node->getChildTokens() as $token) {
            assert($token instanceof Token);
            return $token->kind;
        }

        return 0;
    }
}
