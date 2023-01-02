<?php

namespace Phpactor\Extension\LanguageServerHover\Renderer;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

class MemberDocblock
{
    public function __construct(private ReflectionMember $member)
    {
    }

    /**
     * @return ReflectionMember[]
     */
    public function ancestorsAndSelf(): array
    {
        return array_filter(array_reverse($this->buildAncestors($this->member->class())), function (ReflectionMember $member) {
            return $member->docblock()->isDefined() && !empty(trim($member->docblock()->raw()));
        });
    }

    public function member(): ReflectionMember
    {
        return $this->member;
    }

    private function buildAncestors(?ReflectionClassLike $classLike, array $ancestors = []): array
    {
        if (null === $classLike) {
            return $ancestors;
        }

        $name = $classLike->name()->full();

        if (isset($ancestors[$name])) {
            return $ancestors;
        }

        if ($classLike instanceof ReflectionClass) {
            if ($classLike->methods()->belongingTo($classLike->name())->has($this->member->name())) {
                $ancestors[$name] = $classLike->methods()->belongingTo($classLike->name())->get($this->member->name());
            }

            $ancestors = $this->buildAncestors($classLike->parent(), $ancestors);

            foreach ($classLike->interfaces() as $interface) {
                $ancestors = $this->buildAncestors($interface, $ancestors);
            }

            return $ancestors;
        }

        if ($classLike instanceof ReflectionInterface) {
            if ($classLike->methods()->belongingTo($classLike->name())->has($this->member->name())) {
                $ancestors[$name] = $classLike->methods()->belongingTo($classLike->name())->get($this->member->name());
            }

            foreach ($classLike->parents() as $parent) {
                $ancestors = $this->buildAncestors($parent, $ancestors);
            }

            return $ancestors;
        }

        return [];
    }
}
