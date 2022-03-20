<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

class VirtualReflectionMemberCollection extends AbstractReflectionCollection implements ReflectionMemberCollection
{
    public static function fromMembers(array $members): ReflectionMemberCollection
    {
        return new static($members);
    }

    public function byName(string $name): ReflectionMemberCollection
    {
        if ($this->has($name)) {
            return new static([ $this->get($name) ]);
        }

        return new static([]);
    }

    public function methods(): ReflectionMethodCollection
    {
        return VirtualReflectionMethodCollection::fromReflectionMethods(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionMethod;
        }));
    }

    public function byVisibilities(array $visibilities): ReflectionMemberCollection
    {
        $items = [];
        foreach ($this as $key => $item) {
            foreach ($visibilities as $visibility) {
                if ($item->visibility() != $visibility) {
                    continue;
                }

                $items[$key] = $item;
            }
        }

        return new static($items);
    }

    public function belongingTo(ClassName $class): ReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $item) use ($class) {
            return $item->declaringClass()->name() == $class;
        }));
    }

    public function atOffset(int $offset): ReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $item) use ($offset) {
            return $item->position()->start() <= $offset && $item->position()->end() >= $offset;
        }));
    }

    public function virtual(): ReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $member) {
            return true === $member->isVirtual();
        }));
    }

    public function real(): ReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $member) {
            return false === $member->isVirtual();
        }));
    }

    public function properties(): ReflectionPropertyCollection
    {
        return new VirtualReflectionPropertyCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionProperty;
        }));
    }

    public function byMemberType(string $type): ReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $member) use ($type) {
            return $type === $member->memberType();
        }));
    }

    protected function collectionType(): string
    {
        return ReflectionMemberCollection::class;
    }
}
