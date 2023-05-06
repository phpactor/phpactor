<?php

namespace Phpactor\Extension\PHPUnit\MemberContextResolver;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class AssertMemberContextResolver implements MemberContextResolver
{
    public function resolveMemberContext(Reflector $reflector, ReflectionMember $member, Type $type, ?FunctionArguments $arguments): ?Type
    {
        dd('I am here!');
        if ($member->memberType() !== ReflectionMember::TYPE_METHOD) {
            return null;
        }

        if ($member->name() !== 'assertInstanceOf') {
            return null;
        }

        if (!$member->class()->isInstanceOf(ClassName::fromString('PHPUnit\Framework\Assert'))) {
            return null;
        }

        if (null === $arguments) {
            return null;
        }

        if (count($arguments) === 0) {
            return null;
        }

        $argument = $arguments->at(0);

        if (!$argument instanceof StringLiteralType) {
            return null;
        }

        return TypeFactory::fromStringWithReflector($argument->value(), $reflector);
    }
}
