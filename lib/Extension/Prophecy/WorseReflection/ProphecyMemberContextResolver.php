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
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Reflector;
use Prophecy\Prophecy\MethodProphecy;

class ProphecyMemberContextResolver implements MemberContextResolver
{
    const PROPHECY_CLASS = 'Prophecy\Prophecy\ProphecyInterface';

    public function resolveMemberContext(
        Reflector $reflector,
        string $memberType,
        string $memberName,
        Type $containerType,
        ?FunctionArguments $arguments
    ): ?Type {
        if ($memberType !== ReflectionMember::TYPE_METHOD) {
            return null;
        }

        if ($memberName !== 'prophesize') {
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
        $innerReflection = $reflector->reflectClassLike($innerType->name());

        $type = new GenericClassType($reflector, ClassName::fromString('Prophecy\Prophecy\ObjectProphecy'), [
            $innerType
        ]);

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
