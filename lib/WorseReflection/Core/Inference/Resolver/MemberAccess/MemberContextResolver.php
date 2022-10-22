<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

interface MemberContextResolver
{
    public function resolveMemberContext(Reflector $reflector, string $memberType, string $memberName, Type $containerType, ?FunctionArguments $arguments): ?Type;
}
