<?php

namespace Phpactor\WorseReflection\Core\Util;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\ResolvedName;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Reflector;

class NodeUtil
{
    private const RESERVED_NAMES = [
        'iterable',
        'resource',
    ];

    /**
     * @param array<string, ResolvedName> $importTable
     */
    public static function resolveNameFromImportTable(Node $node, array $importTable): ?ResolvedName
    {
        if (!$node instanceof QualifiedName) {
            return null;
        }
        $content = $node->getFileContents();
        $nameParts = $node->getNameParts();
        if (count($nameParts) === 0) {
            return null;
        }
        $base = $nameParts[0]->getText($content);

        if (isset($importTable[$base])) {
            $resolvedName = $importTable[$base];
            $resolvedName->addNameParts(\array_slice($node->getNameParts(), 1), $content);
            return $resolvedName;
        }
        return null;
    }

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
                    // Resolving anonymous classes is not currently supported
                    return null;
                }
            }

            return self::nodeContainerClassLikeDeclaration($ancestor);
        }

        /** @var ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null */
        return $ancestor;
    }

    /**
     * @param Token|Node|ObjectCreationExpression|mixed $nodeOrToken
     */
    public static function nameFromTokenOrNode(Node $node, $nodeOrToken): string
    {
        if ($nodeOrToken instanceof Token) {
            return (string)$nodeOrToken->getText($node->getFileContents());
        }
        if ($nodeOrToken instanceof ObjectCreationExpression) {
            return 'class@anonymous:'.$nodeOrToken->getStartPosition();
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

    public static function shortName(Node $node): string
    {
        if (!$node instanceof QualifiedName) {
            return '';
        }

        $parts = $node->getNameParts();
        $last = array_pop($parts);

        if (!$last instanceof Token) {
            return '';
        }

        return (string)$last->getText($node->getFileContents());
    }

    public static function operatorKindForUnaryExpression(UnaryExpression $node): int
    {
        foreach ($node->getChildTokens() as $token) {
            assert($token instanceof Token);
            return $token->kind;
        }

        return 0;
    }

    /**
     * For debugging: pretty print the AST
     */
    public static function dump(Node $node, int $level = 0): string
    {
        $out = [
            sprintf(
                '%s %d:%d - %s',
                str_repeat('  ', $level) . $node->getNodeKindName(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                str_replace("\n", '\\n', $node->getText()),
            )
        ];

        $level++;
        foreach ($node->getChildNodes() as $child) {
            $out[] = self::dump($child, $level);
        }

        return implode("\n", $out);
    }

    /**
     * @param null|Node|Token $nodeOrToken
     */
    public static function typeFromQualfiedNameLike(Reflector $reflector, Node $node, $nodeOrToken, ?ClassName $classContext = null): Type
    {
        if ($nodeOrToken instanceof Token) {
            $text = (string)$nodeOrToken->getText($node->getFileContents());

            if ($text === 'static' && $classContext) {
                $class = self::nodeContainerClassLikeDeclaration($node);
                return TypeFactory::reflectedClass($reflector, $classContext->__toString());
            }

            return TypeFactory::fromStringWithReflector(
                $text,
                $reflector
            );
        }

        if ($nodeOrToken instanceof QualifiedName) {
            $text = $nodeOrToken->getText();
            if ($nodeOrToken->isUnqualifiedName() && in_array($text, self::RESERVED_NAMES)) {
                return TypeFactory::fromStringWithReflector($text, $reflector);
            }

            if ($text === 'self') {
                return new SelfType();
            }

            if ($text === 'static') {
                $class = self::nodeContainerClassLikeDeclaration($node);
                return TypeFactory::reflectedClass($reflector, $classContext->__toString());
            }

            return TypeFactory::fromStringWithReflector($nodeOrToken->getResolvedName(), $reflector);
        }

        if ($nodeOrToken instanceof QualifiedNameList) {
            $isIntersection = false;
            $types = array_filter(array_map(function ($name) use ($node, $reflector, &$isIntersection, $classContext) {
                if ($name instanceof Token && $name->kind === TokenKind::AmpersandToken) {
                    $isIntersection = true;
                    return false;
                }
                if (null === $name) {
                    return new MissingType();
                }
                return self::typeFromQualfiedNameLike($reflector, $node, $name, $classContext);
            }, iterator_to_array($nodeOrToken->getElements(), true)), fn ($name) => $name !== false);

            return ($isIntersection ? IntersectionType::fromTypes(...$types) : UnionType::fromTypes(...$types))->reduce();
        }

        return TypeFactory::unknown();
    }

    public static function canAcceptTypeAssertion(Node ...$nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof Variable) {
                return true;
            }

            if ($node instanceof MemberAccessExpression) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the descendant first node after the given offset
     */
    public static function firstDescendantNodeAfterOffset(Node $node, int $offset): Node
    {
        foreach ($node->getDescendantNodes() as $node) {
            if ($node->getStartPosition() > $offset) {
                return $node;
            }
        }

        return $node;
    }
    public static function firstDescendantNodeBeforeOffset(Node $node, int $offset): Node
    {
        $lastNode = null;
        foreach ($node->getDescendantNodes() as $node) {
            if ($node->getStartPosition() >= $offset) {
                return $lastNode ?? $node;
            }
            $lastNode = $node;
        }

        return $node;
    }
    public static function lastDescendantNodeBeforeOffsetOfType(Node $node, int $offset, string $fqn): Node
    {
        $best = null;
        $bestPos = null;
        foreach ($node->getDescendantNodes() as $descendant) {
            if (!$descendant instanceof $fqn) {
                continue;
            }
            if ($descendant->getEndPosition() > $offset) {
                continue;
            }
            if (null === $bestPos || $descendant->getEndPosition() > $bestPos) {
                $best = $descendant;
                $bestPos = $descendant->getEndPosition();
            }
        }

        return $best ?? $node;
    }

    public static function previousSibling(?Node $node): ?Node
    {
        if (null === $node) {
            return null;
        }
        $parent = $node->parent;
        if (null === $parent) {
            return null;
        }
        $previous = null;
        foreach ($parent->getChildNodes() as $childNode) {
            if (null === $previous) {
                $previous = $childNode;
                continue;
            }
            if ($childNode === $node) {
                return $previous;
            }
            $previous = $childNode;
        }

        return null;
    }

    public static function namespace(Node $node): ?string
    {
        $namespace = $node->getNamespaceDefinition();

        if (null === $namespace) {
            return null;
        }

        if (!$namespace->name instanceof QualifiedName) {
            return null;
        }

        return $namespace->name->__toString();
    }

    public static function nullOrMissing(mixed $subject): bool
    {
        if (null === $subject) {
            return true;
        }

        if ($subject instanceof MissingToken) {
            return true;
        }

        return false;
    }

    public static function byteOffsetRangeForNode(Variable $node): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts($node->getStartPosition(), $node->getEndPosition());
    }

    /**
     * @return ?int<0,max>
     */
    public static function argumentOffset(ArgumentExpressionList $argumentExpressionList, ArgumentExpression $argument): ?int
    {
        $offset = 0;
        foreach ($argumentExpressionList->getChildNodes() as $funcArg) {
            if ($argument === $funcArg) {
                return $offset;
            }
            $offset++;
        }

        return null;
    }
}
