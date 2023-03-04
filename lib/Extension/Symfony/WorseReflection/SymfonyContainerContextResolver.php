<?php

namespace Phpactor\Extension\Symfony\WorseReflection;

use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;
use Phpactor\WorseReflection\Reflector;

class SymfonyContainerContextResolver implements MemberContextResolver
{
    const CONTAINER_CLASS = 'Symfony\\Component\\DependencyInjection\\ContainerInterface';

    public function __construct(private SymfonyContainerInspector $inspector)
    {
    }

    public function resolveMemberContext(
        Reflector $reflector,
        ReflectionMember $member,
        Type $type,
        ?FunctionArguments $arguments
    ): ?Type {
        if ($member->memberType() !== ReflectionMember::TYPE_METHOD) {
            return null;
        }

        if ($member->name() !== 'get') {
            return null;
        }

        if (count($arguments) === 0) {
            return null;
        }

        if (!$member->class()->isInstanceOf(ClassName::fromString(self::CONTAINER_CLASS))) {
            return null;
        }

        $argument = $arguments->at(0)->type();
        if ($argument instanceof StringLiteralType) {
            $service = $this->inspector->service($argument->value());
            if (null === $service) {
                return TypeFactory::union(TypeFactory::object(), TypeFactory::null());
            }
            return $service->type;
        }
        if ($argument instanceof ClassStringType && $argument->className()) {
            $service = $this->inspector->service($argument->className()->__toString());
            if (null === $service) {
                return TypeFactory::union(TypeFactory::object(), TypeFactory::null());
            }
            $type = $service->type;
            if ($type instanceof ClassType) {
                $type = $type->asReflectedClasssType($reflector);
            }
            return $type;
        }

        return TypeFactory::undefined();
    }
}
