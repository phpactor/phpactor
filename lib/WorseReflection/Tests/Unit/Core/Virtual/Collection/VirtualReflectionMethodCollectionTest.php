<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Visibility;
use Prophecy\PhpUnit\ProphecyTrait;

class VirtualReflectionMethodCollectionTest extends VirtualReflectionMemberTestCase
{
    use ProphecyTrait;

    public function collection(array $names): ReflectionCollection
    {
        $items = [];
        foreach ($names as $name) {
            $item = $this->prophesize(ReflectionMethod::class);
            $item->name()->willReturn($name);
            $item->visibility()->willReturn(Visibility::public());
            $item->declaringClass()->willReturn($this->declaringClass->reveal());
            $item->class()->willReturn($this->class->reveal());
            $item->isVirtual()->willReturn(true);
            $item->position()->willReturn($this->position);
            $items[] = $item->reveal();
        }
        return VirtualReflectionMethodCollection::fromReflectionMethods($items);
    }
}
