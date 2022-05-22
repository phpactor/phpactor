<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;

interface HasMembers
{
    /**
     * todo: remove this from other interfaces
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function ownMembers(): ReflectionMemberCollection;
}
