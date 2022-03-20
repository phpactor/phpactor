<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection as CoreReflectionMemberCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

/**
 * @method static ReflectionMemberCollection empty(ServiceLocator $serviceLocator)
 */
class ReflectionMemberCollection extends AbstractReflectionCollection implements CoreReflectionMemberCollection
{
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

        return new static($this->serviceLocator, $items);
    }

    public function belongingTo(ClassName $class): CoreReflectionMemberCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $item) use ($class) {
            return $item->declaringClass()->name() == $class;
        }));
    }

    public function atOffset(int $offset): CoreReflectionMemberCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $item) use ($offset) {
            return $item->position()->start() <= $offset && $item->position()->end() >= $offset;
        }));
    }

    public function byName(string $name): CoreReflectionMemberCollection
    {
        if ($this->has($name)) {
            return new self($this->serviceLocator, [ $this->get($name) ]);
        }

        return new self($this->serviceLocator, []);
    }

    public function virtual(): CoreReflectionMemberCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $member) {
            return true === $member->isVirtual();
        }));
    }

    public function real(): CoreReflectionMemberCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $member) {
            return false === $member->isVirtual();
        }));
    }

    public function methods(): ReflectionMethodCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionMethod;
        }));
    }

    public function properties(): ReflectionPropertyCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $member) {
            return $member instanceof ReflectionProperty;
        }));
    }

    public function byMemberType(string $type): CoreReflectionMemberCollection
    {
        return new static($this->serviceLocator, array_filter($this->items, function (ReflectionMember $member) use ($type) {
            return $type === $member->memberType();
        }));
    }

    protected function collectionType(): string
    {
        return CoreReflectionMemberCollection::class;
    }
}
