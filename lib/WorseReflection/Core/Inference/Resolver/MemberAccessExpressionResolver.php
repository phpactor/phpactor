<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\GenericTypeResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MemberAccessExpressionResolver implements Resolver
{
    private GenericTypeResolver $genericResolver;

    public function __construct()
    {
        $this->genericResolver = new GenericTypeResolver();
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression);

        $class = $resolver->resolveNode($frame, $node->dereferencableExpression);

        return self::infoFromMemberAccess($resolver, $frame, $class->type(), $node, $this->genericResolver);
    }

    public static function infoFromMemberAccess(NodeContextResolver $resolver, Frame $frame, Type $classType, Node $node, ?GenericTypeResolver $genericResolver = null): NodeContext
    {
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = NodeUtil::nameFromTokenOrNode($node, $node->memberName);
        $memberType = $node->getParent() instanceof CallExpression ? Symbol::METHOD : Symbol::PROPERTY;

        if ($node->memberName instanceof Node) {
            $memberNameType = $resolver->resolveNode($frame, $node->memberName)->type();
            if ($memberNameType instanceof StringLiteralType) {
                $memberName = $memberNameType->value;
            }
        }

        if (
            Symbol::PROPERTY === $memberType
            && $node instanceof ScopedPropertyAccessExpression
            && is_string($memberName)
            && substr($memberName, 0, 1) !== '$'
        ) {
            $memberType = Symbol::CONSTANT;
        }

        $information = NodeContextFactory::create(
            (string)$memberName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => $memberType,
            ]
        );

        if (Symbol::CONSTANT === $memberType) {
            if ($memberName === 'class') {
                if (!$classType instanceof ClassType) {
                    return $information;
                }
                return $information->withType(TypeFactory::stringLiteral($classType->name()->full()));
            }
        }

        $types = $memberTypes = [];

        // this could be a union or a nullable
        foreach ($classType->classNamedTypes() as $subType) {
            try {
                $reflection = $resolver->reflector()->reflectClassLike($subType->name());
            } catch (NotFound $e) {
                continue;
            }

            foreach ($reflection->members()->byMemberType($memberType)->byName($memberName) as $member) {
                $inferredType = $member->inferredType();

                if ($member instanceof ReflectionProperty) {
                    $inferredType = self::getFrameTypesForPropertyAtPosition(
                        $frame,
                        (string) $memberName,
                        $subType,
                        $node->getEndPosition(),
                    );
                }

                $types[] = $subType;

                if ($genericResolver) {
                    $inferredType = $genericResolver->resolveMemberType($subType, $member, $inferredType);
                }

                // if multiple classes declare a member, always take the "top" one
                $memberTypes[$memberName] = $inferredType;
            }
        }

        $containerType = UnionType::fromTypes(...$types)->reduce();

        if (!$containerType->isDefined()) {
            $containerType = $classType;
        }

        return $information->withContainerType(
            $containerType
        )->withType(
            (new UnionType(...array_values($memberTypes)))->clean()->reduce()
        );
    }

    private static function getFrameTypesForPropertyAtPosition(
        Frame $frame,
        string $propertyName,
        Type $classType,
        int $position
    ): ?Type {
        if (!$classType instanceof ClassType) {
            return null;
        }

        $variable = $frame->properties()
            ->lessThanOrEqualTo($position)
            ->byName($propertyName)
            ->lastOrNull();

        if (null === $variable) {
            return null;
        }

        return $variable->type();
    }
}
