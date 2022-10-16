<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Type;

interface MemberContextResolver
{
    public function resolveMemberContext(string $memberType, string $memberName, Type $containerType, FunctionArguments $arguments): ?Type;
}
