<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

/**
 * @implements ReflectionMemberProvider<ReflectionMember>
 */
class DocblockMemberProvider implements ReflectionMemberProvider
{
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        return VirtualReflectionMemberCollection::fromMembers([])
            ->merge(VirtualReflectionMemberCollection::fromMembers(iterator_to_array($class->docblock()->methods($class))))
            ->merge(VirtualReflectionMemberCollection::fromMembers(iterator_to_array($class->docblock()->properties($class))));
    }
}
