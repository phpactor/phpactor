<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual\Collection;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Visibility;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection collection()
 */
abstract class VirtualReflectionMemberTestCase extends AbstractReflectionCollectionTestCase
{
    use ProphecyTrait;
    
    protected ObjectProphecy $declaringClass;
    
    protected ObjectProphecy $class;

    protected $position;

    public function setUp(): void
    {
        $this->declaringClass = $this->prophesize(ReflectionClass::class);
        $this->class = $this->prophesize(ReflectionClass::class);
        $this->position = Position::fromStartAndEnd(0, 10);
    }

    public function testByName(): void
    {
        $collection = $this->collection(['one', 'two'])->byName('one');
        $this->assertCount(1, $collection);
    }

    public function testByVisiblities(): void
    {
        $collection = $this->collection(['one', 'two'])->byVisibilities([
            Visibility::public()
        ]);
        $this->assertCount(2, $collection);
    }

    public function testBelongingTo(): void
    {
        $belongingTo = ClassName::fromString('Hello');
        $this->declaringClass->name()->willReturn($belongingTo);
        $collection = $this->collection(['one', 'two'])->belongingTo($belongingTo);
        $this->assertCount(2, $collection);
    }

    public function testAtOffset(): void
    {
        $collection = $this->collection(['one', 'two'])->atoffset(0);
        $this->assertcount(2, $collection);
    }

    public function testVirtual(): void
    {
        $collection = $this->collection(['one', 'two']);
        $this->assertCount(2, $collection->virtual());
    }

    public function testReal(): void
    {
        $collection = $this->collection(['one', 'two']);
        $this->assertCount(0, $collection->real());
    }
}
