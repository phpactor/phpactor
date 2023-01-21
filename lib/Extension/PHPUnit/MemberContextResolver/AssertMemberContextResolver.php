<?php

namespace Phpactor\Extension\PHPUnit\MemberContextResolver;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Reflector;

class AssertMemberContextResolver implements MemberContextResolver
{
    public function resolveMemberContext(Reflector $reflector, ReflectionMember $member, ?FunctionArguments $arguments): ?Type
    {
        if ($member->memberType() !== ReflectionMember::TYPE_METHOD) {
            return null;
        }

        if ($member->name() !== 'assertInstanceOf') {
            return null;
        }

        if (count($arguments) === 0) {
            return null;
        }

        if (!$member->class()->isInstanceOf(ClassName::fromString('PHPUnit\Framework\Assert'))) {
            return null;
        }

        $argument = $arguments->at(0);

        return null;
    }
}
