<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionMemberCollection as CoreReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;

/**
 * @method static ReflectionMemberCollection empty()
 * @implements CoreReflectionMemberCollection<ReflectionMember>
 */
class ReflectionMemberCollection extends AbstractReflectionCollection implements CoreReflectionMemberCollection
{
    /**
     * @param ReflectionMember[] $members
     * @return CoreReflectionMemberCollection<ReflectionMember>
     */
    public static function fromMembers(array $members): CoreReflectionMemberCollection
    {
        return new self($members);
    }

    /**
     * @return CoreReflectionMemberCollection<ReflectionMember>
     * @param Visibility[] $visibilities
     */
    public function byVisibilities(array $visibilities): CoreReflectionMemberCollection
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

    public function belongingTo(ClassName $class): CoreReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $item) use ($class) {
            return $item->declaringClass()->name() == $class;
        }));
    }

    public function atOffset(int $offset): CoreReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $item) use ($offset) {
            return $item->position()->start() <= $offset && $item->position()->end() >= $offset;
        }));
    }

    public function byName(string $name): CoreReflectionMemberCollection
    {
        if ($this->has($name)) {
            return new self([ $this->get($name) ]);
        }

        return new static([]);
    }

    public function virtual(): CoreReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $member) {
            return true === $member->isVirtual();
        }));
    }

    public function real(): CoreReflectionMemberCollection
    {
        return new self(array_filter($this->items, function (ReflectionMember $member) {
            return false === $member->isVirtual();
        }));
    }

    public function methods(): CoreReflectionMethodCollection
    {
        return new ReflectionMethodCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionMethod;
        }));
    }

    public function properties(): CoreReflectionPropertyCollection
    {
        return new ReflectionPropertyCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionProperty;
        }));
    }

    public function byMemberType(string $type): CoreReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $member) use ($type) {
            return $type === $member->memberType();
        }));
    }

    protected function collectionType(): string
    {
        return CoreReflectionMemberCollection::class;
    }
}
