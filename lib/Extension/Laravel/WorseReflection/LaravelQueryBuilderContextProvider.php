<?php

namespace Phpactor\Extension\Laravel\WorseReflection;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

class LaravelQueryBuilderContextProvider implements MemberContextResolver
{
    public function __construct(private LaravelContainerInspector $inspector)
    {
    }

    public function resolveMemberContext(Reflector $reflector, ReflectionMember $member, Type $type, ?FunctionArguments $arguments): ?Type
    {
        return $type;
    }
}
