<?php

namespace Phpactor\Extension\Prophecy\WorseReflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Reflector;

class ProphecyMemberContextResolver implements MemberContextResolver
{
    const PROPHECY_CLASS = 'Prophecy\Prophecy\ProphecyInterface';

    public function resolveMemberContext(
        Reflector $reflector,
        ReflectionMember $member,
        ?FunctionArguments $arguments
    ): ?Type {
        $memberType = $member->inferredType();
        if ($memberType instanceof GenericClassType && $memberType->instanceof(TypeFactory::reflectedClass($reflector, 'Prophecy\Prophecy\ObjectProphecy'))->isTrue()) {
            return $this->fromGeneric($reflector, $memberType);
        }

        return $this->fromProphesize($reflector, $member, $arguments);
    }

    private function fromProphesize(
        Reflector $reflector,
        ReflectionMember $member,
        ?FunctionArguments $arguments
    ): ?Type {
        if (!$member instanceof ReflectionMethod) {
            return null;
        }

        if ($member->name() !== 'prophesize') {
            return null;
        }

        if ($arguments->count() !== 1) {
            return null;
        }

        $arg = $arguments->at(0)->type();

        if (!$arg instanceof ClassStringType) {
            return null;
        }

        $innerType = TypeFactory::class($arg->className());

        $type = new GenericClassType($reflector, ClassName::fromString('Prophecy\Prophecy\ObjectProphecy'), [$innerType]);

        return $this->fromGeneric($reflector, $type);
    }

    private function fromGeneric(Reflector $reflector, GenericClassType $type): Type
    {
        $innerType = $type->arguments()[0];
        if (!$innerType instanceof ClassType) {
            return TypeFactory::undefined();
        }
        $innerReflection = $reflector->reflectClassLike($innerType->name());
        return $type->mergeMembers($innerReflection->members()->map(function (ReflectionMember $member) use ($reflector) {
            if (!$member instanceof ReflectionMethod) {
                return $member;
            }
            return VirtualReflectionMethod::fromReflectionMethod($member)->withInferredType(
                new GenericClassType($reflector, ClassName::fromString('Prophecy\Prophecy\MethodProphecy'), [
                    $member->inferredType()
                ])
            );
        }));
    }
}
