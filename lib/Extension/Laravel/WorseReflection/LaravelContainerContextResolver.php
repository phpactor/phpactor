<?php

namespace Phpactor\Extension\Laravel\WorseReflection;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
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

class LaravelContainerContextResolver implements MemberContextResolver
{
    public const CONTAINER_CLASSES = [
        'Illuminate\\Contracts\\Foundation\\Application',
        'Illuminate\Foundation\Application',
        'Illuminate\\Support\\Facades\\App'
    ];

    public function __construct(private LaravelContainerInspector $inspector)
    {
    }

    public function resolveMemberContext(Reflector $reflector, ReflectionMember $member, Type $type, ?FunctionArguments $arguments): ?Type
    {
        $isMatch = false;

        foreach (self::CONTAINER_CLASSES as $class) {
            if ($member->class()->isInstanceOf(ClassName::fromString($class))) {
                $isMatch = true;
                break;
            }
        }

        if (!$isMatch) {
            return null;
        }

        if ($member->name() === 'get' || $member->name() === 'make') {
            $argument = $arguments->at(0)->type();
        } elseif ($member->name() === 'MAIN_REQUEST') {
            // @todo: Here we are in app('something')-><CUR> but we cannot get the 'something' part here.
            return null;
        } else {
            return null;
        }

        if ($argument instanceof StringLiteralType) {
            $service = $this->inspector->service($argument->value());
            if (null === $service) {
                return TypeFactory::union(TypeFactory::object(), TypeFactory::null());
            }
            return $service->asReflectedClasssType($reflector);
        }
        if ($argument instanceof ClassStringType && $argument->className()) {
            $serviceClassType = $this->inspector->service($argument->className()->__toString());
            if (!$serviceClassType) {
                $serviceClassType = TypeFactory::fromString($argument->className()->__toString());
            }
            if ($serviceClassType instanceof ClassType) {
                return $serviceClassType->asReflectedClasssType($reflector);
            }
        }

        return TypeFactory::undefined();
    }
}
