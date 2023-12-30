<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

interface MemberContextResolver
{
    public function resolveMemberContext(Reflector $reflector, ReflectionMember $member, Type $type, ?FunctionArguments $arguments): ?Type;
}
