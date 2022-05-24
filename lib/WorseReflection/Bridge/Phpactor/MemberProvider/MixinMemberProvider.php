<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider;

use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class MixinMemberProvider implements ReflectionMemberProvider
{
    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($class->docblock()->mixins() as $mixin) {
            if (!$mixin instanceof ReflectedClassType) {
                continue;
            }

            $reflection = $mixin->reflectionOrNull();

            if (null === $reflection) {
                continue;
            }

            $collections[] = $reflection->methods($class);

            if ($reflection instanceof ReflectionClass) {
                $collections[] = $reflection->properties($class);
            }
        }
        return ChainReflectionMemberCollection::fromCollections($collections);
    }
}
