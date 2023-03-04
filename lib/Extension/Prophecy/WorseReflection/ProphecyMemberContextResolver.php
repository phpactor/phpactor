<?php

namespace Phpactor\Extension\Prophecy\WorseReflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
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
    const OBJECT_PROPHECY_METHOD_NAME = 'getObjectProphecy';


    public function resolveMemberContext(
        Reflector $reflector,
        ReflectionMember $member,
        Type $type,
        ?FunctionArguments $arguments
    ): ?Type {
        if ($type instanceof GenericClassType && $type->instanceof(TypeFactory::reflectedClass($reflector, 'Prophecy\Prophecy\ObjectProphecy'))->isTrue()) {
            return $this->fromGeneric($reflector, $type);
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

        if (null === $arguments) {
            return null;
        }

        if ($arguments->count() !== 1) {
            return null;
        }

        $arg = $arguments->at(0)->type();

        if (!$arg instanceof ClassStringType) {
            return null;
        }

        $className = $arg->className();

        if (null === $className) {
            return null;
        }

        $innerType = TypeFactory::class($className);

        $type = new GenericClassType($reflector, ClassName::fromString('Prophecy\Prophecy\ObjectProphecy'), [$innerType]);

        return $this->fromGeneric($reflector, $type);
    }

    private function fromGeneric(Reflector $reflector, GenericClassType $type): Type
    {
        $innerType = $type->arguments()[0];
        if (!$innerType instanceof ClassType) {
            return TypeFactory::undefined();
        }

        try {
            $innerReflection = $reflector->reflectClassLike($innerType->name());
        } catch (NotFound) {
            return TypeFactory::unknown();
        }
        return $type->mergeMembers($innerReflection->members()->map(function (ReflectionMember $member) use ($reflector, $innerType) {
            if (!$member instanceof ReflectionMethod) {
                return $member;
            }
            return VirtualReflectionMethod::fromReflectionMethod($member)->withInferredType(
                new GenericClassType($reflector, ClassName::fromString('Prophecy\Prophecy\MethodProphecy'), [
                    $innerType
                ])
            );
        }));
    }
}
