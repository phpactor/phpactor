<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider;

use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class DocblockMemberProvider implements ReflectionMemberProvider
{
    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        return ChainReflectionMemberCollection::fromCollections([
            $class->docblock()->methods($class),
            $class->docblock()->properties($class),
        ]);
    }
}
