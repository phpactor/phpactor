<?php

namespace Phpactor\Extension\Symfony\WorseReflection;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;

class SymfonyContainerContextResolver implements MemberContextResolver
{
    public function resolveMemberContext(
        string $memberType,
        string $memberName,
        Type $containerType
    ): ?NodeContext
    {
        if ($memberType !== ReflectionMember::TYPE_METHOD) {
            return null;
        }

        if ($memberName !== 'get') {
            return null;
        }

        return null;
    }
}
