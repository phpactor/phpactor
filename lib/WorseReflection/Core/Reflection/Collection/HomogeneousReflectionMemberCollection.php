<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;

/**
 * @template T of ReflectionMember
 * @extends AbstractReflectionCollection<T>
 * @implements ReflectionMemberCollection<T>
 */
class HomogeneousReflectionMemberCollection extends AbstractReflectionCollection implements ReflectionMemberCollection
{
    /**
     * @return static
     * @param ReflectionMember[] $members
     */
    public static function fromMembers(array $members): HomogeneousReflectionMemberCollection
    {
        return new static($members);
    }

    /**
     * @return static
     * @param Visibility[] $visibilities
     */
    public function byVisibilities(array $visibilities): HomogeneousReflectionMemberCollection
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

    /**
     * @return static
     */
    public function belongingTo(ClassName $class): HomogeneousReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $item) use ($class) {
            return $item->declaringClass()->name() == $class;
        }));
    }

    /**
     * @return static
     */
    public function atOffset(int $offset): HomogeneousReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $item) use ($offset) {
            return $item->position()->start()->toInt() <= $offset && $item->position()->end()->toInt() >= $offset;
        }));
    }

    /**
     * @return static
     */
    public function byName(string $name): HomogeneousReflectionMemberCollection
    {
        if ($this->has($name)) {
            return new static([ $this->get($name) ]);
        }

        return new static([]);
    }

    /**
     * @return static
     */
    public function virtual(): HomogeneousReflectionMemberCollection
    {
        return new static(array_filter($this->items, fn (ReflectionMember $member) => $member->isVirtual()));
    }

    /**
     * @return static
     */
    public function real(): HomogeneousReflectionMemberCollection
    {
        return new static(array_filter($this->items, fn (ReflectionMember $member) => !$member->isVirtual()));
    }

    public function methods(): ReflectionMethodCollection
    {
        return new ReflectionMethodCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionMethod;
        }));
    }

    public function constants(): ReflectionConstantCollection
    {
        return new ReflectionConstantCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionConstant;
        }));
    }

    public function properties(): ReflectionPropertyCollection
    {
        return new ReflectionPropertyCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionProperty;
        }));
    }

    public function enumCases(): ReflectionEnumCaseCollection
    {
        return new ReflectionEnumCaseCollection(array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionEnumCase;
        }));
    }

    /**
     * @return static
     */
    public function byMemberType(string $type): HomogeneousReflectionMemberCollection
    {
        return new static(array_filter($this->items, function (ReflectionMember $member) use ($type) {
            return $type === $member->memberType();
        }));
    }

    public function map(Closure $mapper)
    {
        return new static(array_map($mapper, $this->items));
    }

    protected function collectionType(): string
    {
        return HomogeneousReflectionMemberCollection::class;
    }
}
