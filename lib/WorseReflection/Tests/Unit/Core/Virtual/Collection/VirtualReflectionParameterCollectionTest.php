<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual\Collection;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionParameterCollection;
use Prophecy\PhpUnit\ProphecyTrait;

class VirtualReflectionParameterCollectionTest extends AbstractReflectionCollectionTestCase
{
    use ProphecyTrait;

    public function collection(array $names): ReflectionCollection
    {
        $items = [];
        foreach ($names as $name) {
            $item = $this->prophesize(ReflectionParameter::class);
            $item->name()->willReturn($name);
            $items[] = $item->reveal();
        }
        return VirtualReflectionParameterCollection::fromReflectionParameters($items);
    }
}
