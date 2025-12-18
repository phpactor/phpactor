<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\DelimitedList\TraitSelectOrAliasClauseList;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Context\MethodCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
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
    /**
     * @param MemberContextResolver[] $memberResolvers
     */
    public function __construct(
        private GenericMapResolver $resolver,
        private array $memberResolvers
    ) {
    }

    public function infoFromMemberAccess(NodeContextResolver $resolver, Frame $frame, Type $classType, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = NodeUtil::nameFromTokenOrNode($node, $node->memberName);
        $memberTypeName = $node->getParent() instanceof CallExpression ? Symbol::METHOD : Symbol::PROPERTY;

        // support trait method-alias clauses, e.g. use  use A, B {A::foobar insteadof B; B::bigTalk insteadof A;}
        if (
            $memberTypeName === Symbol::PROPERTY &&
            $node->parent?->parent &&
            $node->parent->parent instanceof TraitSelectOrAliasClauseList
        ) {
            $memberTypeName = Symbol::METHOD;
        }

        if ($node->memberName instanceof Node) {
            $memberNameType = $resolver->resolveNode($frame, $node->memberName)->type();
            if ($memberNameType instanceof StringLiteralType) {
                $memberName = $memberNameType->value;
            }
        }

        if (
            Symbol::PROPERTY === $memberTypeName
            && $node instanceof ScopedPropertyAccessExpression
            && is_string($memberName)
            && !str_starts_with($memberName, '$')
        ) {
            $memberTypeName = Symbol::CONSTANT;
        }

        $context = NodeContextFactory::create(
            (string)$memberName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => $memberTypeName,
            ]
        );

        if (Symbol::CONSTANT === $memberTypeName) {
            if ($memberName === 'class') {
                if (!$classType instanceof ClassType) {
                    return $context;
                }
                return $context->withType(TypeFactory::classString($classType->name()->full()));
            }

            $constantAssignment = $node->getParent();
            // If you're trying to assign a constant to itself like "const T = self::T;"
            if ($constantAssignment instanceof ConstElement && $constantAssignment->getName() === $memberName) {
                return $context;
            }
        }

        [ $containerType, $memberType, $member, $arguments ] = $this->resolveContainerMemberType(
            $resolver,
            $frame,
            $node,
            $classType,
            $memberTypeName,
            $memberName
        );

        if (!$containerType->isDefined()) {
            $containerType = $classType;
        }

        if ($member instanceof ReflectionMember) {
            if ($member instanceof ReflectionMethod) {
                if (NodeUtil::isFirstClassCallable($node->parent)) {
                    return $context->withType(new ClosureType(
                        $resolver->reflector(),
                        $member->parameters()->types()->toArray(),
                        $member->type(),
                    ));
                }

                return new MethodCallContext(
                    $context->symbol(),
                    $memberType->reduce(),
                    $containerType,
                    ByteOffsetRange::fromInts($node->memberName->getStartPosition(), $node->memberName->getEndPosition()),
                    $member,
                    $arguments,
                );
            }
            return new MemberAccessContext(
                $context->symbol(),
                $memberType->reduce(),
                $containerType,
                ByteOffsetRange::fromInts($node->memberName->getStartPosition(), $node->memberName->getEndPosition()),
                $member,
                $arguments,
            );
        }

        return $context->withContainerType(
            $containerType
        )->withType($memberType->reduce());
    }

    /**
     * @return array{Type,Type,?ReflectionMember,?FunctionArguments}
     */
    private function resolveContainerMemberType(
        NodeContextResolver $resolver,
        Frame $frame,
        Node $node,
        Type $classType,
        string $memberTypeName,
        string $memberName
    ): array {
        $types = [];
        $memberType = TypeFactory::undefined();
        $member = null;

        $arguments = $this->resolveArguments($resolver, $frame, $node->parent);
        // this could be a union or a nullable
        foreach ($classType->expandTypes()->classLike() as $subType) {
            // upcast to ClassType to reflected type
            if (get_class($subType) === ClassType::class) {
                /** @phpstan-ignore-next-line */
                $subType = $subType->asReflectedClasssType($resolver->reflector());
            }

            try {
                $reflection = $resolver->reflector()->reflectClassLike($subType->name());
            } catch (NotFound) {
                continue;
            }

            $types[] = $subType;

            if ($reflection instanceof ReflectionEnum && $memberTypeName === ReflectionMember::TYPE_CONSTANT) {
                foreach ($subType->members()->byMemberType(ReflectionMember::TYPE_CASE)->byName($memberName) as $member) {
                    // if multiple classes declare a member, always take the "top" one
                    $memberType = $this->resolveMemberType($resolver, $frame, $member, $arguments, $node, $subType);
                    break;
                }
            }
            if ($reflection instanceof ReflectionEnum && $memberName === 'cases') {
                $memberType = TypeFactory::array(TypeFactory::reflectedClass($resolver->reflector(), $reflection->name()));
                break;
            }

            foreach ($subType->members()->byMemberType($memberTypeName)->byName($memberName) as $member) {
                // if multiple classes declare a member, always take the "top" one
                $memberType = $this->resolveMemberType($resolver, $frame, $member, $arguments, $node, $subType);
                break;
            }
        }

        if ($member instanceof ReflectionMethod && $arguments) {
            $byReference = $member->parameters()->passedByReference();

            if ($byReference->count()) {
                foreach ($byReference as $parameter) {
                    $argument = $arguments->at($parameter->index());
                    $frame->locals()->set(new Variable(
                        name: $argument->symbol()->name(),
                        offset: $argument->symbol()->position()->start()->toInt(),
                        type: $parameter->type(),
                        wasAssigned: false /** $wasAssigned bool */,
                        wasDefined: true /** $wasDefined bool */
                    ));
                }
            }
        }

        $containerType = UnionType::fromTypes(...$types)->reduce();
        return [$containerType, $memberType, $member, $arguments];
    }

    private function resolveMemberType(NodeContextResolver $resolver, Frame $frame, ReflectionMember $member, ?FunctionArguments $arguments, Node $node, Type $subType): Type
    {
        $inferredType = $member->inferredType();
        $declaringClass = self::declaringClass($member);

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

        if ($arguments && $member instanceof ReflectionMethod) {
            try {
                $declaringMember = $declaringClass->members()->byMemberType($member->memberType())->byName($member->name())->first();
                if ($declaringMember instanceof ReflectionMethod) {
                    $templateMap = $declaringMember->docblock()->templateMap();
                    if (count($templateMap)) {
                        $inferredType = $this->combineMethodTemplateVars($arguments, $templateMap, $declaringMember, $inferredType);
                    }
                }
            } catch (NotFound) {
            }
        }

        if (count($declaringClass->docblock()->templateMap())) {
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

        foreach ($this->memberResolvers as $memberResolver) {
            if (null !== $customType = $memberResolver->resolveMemberContext($resolver->reflector(), $member, $inferredType, $arguments)) {
                $inferredType = $customType;
            }
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
            ->byName($propertyName)
            ->lessThanOrEqualTo($position)
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

    private function resolveArguments(NodeContextResolver $resolver, Frame $frame, ?Node $node): ?FunctionArguments
    {
        if (!$node || !$node instanceof CallExpression) {
            return null;
        }

        return FunctionArguments::fromList($resolver, $frame, $node->argumentExpressionList);
    }

    private function combineMethodTemplateVars(FunctionArguments $arguments, TemplateMap $templateMap, ReflectionMethod $member, Type $type): Type
    {
        $templateMap = $this->resolver->mergeParameters($templateMap, $member->parameters(), $arguments);
        $type = $type->map(function (Type $type) use ($templateMap): Type {
            return $templateMap->getOrGiven($type);
        });

        return $type;
    }
}
