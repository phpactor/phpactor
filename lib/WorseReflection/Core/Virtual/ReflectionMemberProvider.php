<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;

interface ReflectionMemberProvider
{
    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection;
}
