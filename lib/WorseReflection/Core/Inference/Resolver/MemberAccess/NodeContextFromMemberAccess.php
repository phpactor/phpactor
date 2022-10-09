<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\TraitSelectOrAliasClauseList;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\GlobbedConstantUnionType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class NodeContextFromMemberAccess
{
    private GenericMapResolver $resolver;

    public function __construct(GenericMapResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function infoFromMemberAccess(NodeContextResolver $resolver, Frame $frame, Type $classType, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = NodeUtil::nameFromTokenOrNode($node, $node->memberName);
        $memberType = $node->getParent() instanceof CallExpression ? Symbol::METHOD : Symbol::PROPERTY;

        // support trait method-alias clauses, e.g. use  use A, B {A::foobar insteadof B; B::bigTalk insteadof A;}
        if ($memberType === Symbol::PROPERTY && $node->parent->parent && $node->parent->parent instanceof TraitSelectOrAliasClauseList) {
            $memberType = Symbol::METHOD;
        }

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
                return $information->withType(TypeFactory::classString($classType->name()->full()));
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
            $types[] = $subType;

            if ($reflection instanceof ReflectionEnum && $memberType === 'constant') {
                foreach ($reflection->members()->byMemberType('enum')->byName($memberName) as $member) {
                    // if multiple classes declare a member, always take the "top" one
                    $memberTypes[$memberName] = $this->resolveMemberType($frame, $member, $node, $subType);
                    break;
                }
            }

            foreach ($reflection->members()->byMemberType($memberType)->byName($memberName) as $member) {
                // if multiple classes declare a member, always take the "top" one
                $memberTypes[$memberName] = $this->resolveMemberType($frame, $member, $node, $subType);
                break;
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

    private function resolveMemberType(Frame $frame, ReflectionMember $member, Node $node, Type $subType): Type
    {
        $inferredType = $member->inferredType();

        if ($member instanceof ReflectionProperty) {
            $propertyType = self::getFrameTypesForPropertyAtPosition(
                $frame,
                $member->name(),
                $subType,
                $node->getEndPosition(),
            );
            if ($propertyType) {
                $inferredType = $propertyType;
            }
        }


        $declaringClass = self::declaringClass($member);

        if (count($declaringClass->templateMap())) {
            $templateMap = $this->resolver->resolveClassTemplateMap($subType, $declaringClass->name(), $subType instanceof GenericClassType ? $subType->arguments() : []);
            $inferredType = $inferredType->map(function (Type $type) use ($templateMap): Type {
                if ($templateMap && $templateMap->has($type->short())) {
                    return $templateMap->get($type->short());
                }
                return $type;
            });
        }

        // unwrap static and self types (including $this which extends Static) and any nested globbed constant unions
        $inferredType = $inferredType->map(function (Type $type) {
            if ($type instanceof StaticType || $type instanceof SelfType) {
                return $type->type();
            }
            if ($type instanceof GlobbedConstantUnionType) {
                return $type->toUnion();
            }
            return $type;
        });

        // expand globbed constants
        if ($inferredType instanceof GlobbedConstantUnionType) {
            $inferredType = $inferredType->toUnion();
        }

        return $inferredType;
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

    private static function declaringClass(ReflectionMember $member): ReflectionClassLike
    {
        $reflectionClass = $member->declaringClass();

        if (!$reflectionClass instanceof ReflectionClass) {
            return $reflectionClass;
        }

        $interface = self::searchInterfaces($reflectionClass->interfaces(), $member->name());

        if (!$interface) {
            return $reflectionClass;
        }

        return $interface;
    }

    private static function searchInterfaces(ReflectionInterfaceCollection $collection, string $memberName): ?ReflectionInterface
    {
        foreach ($collection as $interface) {
            if ($interface->methods()->has($memberName)) {
                return $interface;
            }

            if (null !== $interface = self::searchInterfaces($interface->parents(), $memberName)) {
                return $interface;
            }
        }

        return null;
    }
}
