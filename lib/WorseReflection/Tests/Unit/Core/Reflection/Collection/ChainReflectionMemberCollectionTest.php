<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Reflection\Collection;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Visibility;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Traversable;

class ChainReflectionMemberCollectionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $collection1;

    private ObjectProphecy $collection2;

    private ObjectProphecy $member1;

    /**
     * @var ObjectProphecy<ReflectionMemberCollection>
     */
    private ObjectProphecy $collection3;

    /**
     * @var ObjectProphecy<ReflectionMemberCollection>
     */
    private ObjectProphecy $collection4;

    public function setUp(): void
    {
        $this->collection1 = $this->prophesize(ReflectionMemberCollection::class);
        $this->collection2 = $this->prophesize(ReflectionMemberCollection::class);
        $this->collection3 = $this->prophesize(ReflectionMemberCollection::class);
        $this->collection4 = $this->prophesize(ReflectionMemberCollection::class);

        $this->member1 = $this->prophesize(ReflectionMember::class);
    }

    public function testIsIterable(): void
    {
        $collection = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal()
        ]);

        $this->collection1->getIterator()->willReturn(new ArrayIterator([1]));
        $this->collection2->getIterator()->willReturn(new ArrayIterator([2]));

        $this->assertInstanceOf(Traversable::class, $collection->getIterator());
        $iterator = $collection->getIterator();
        $this->assertEquals(1, $iterator->current());
        $iterator->next();
        $this->assertEquals(2, $iterator->current());
    }

    public function testItReturnsTheCount(): void
    {
        $collection = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal()
        ]);

        $this->collection1->count()->willReturn(1);
        $this->collection2->count()->willReturn(2);
        $this->assertCount(3, $collection);
    }

    public function testItMergesAnotherCollection(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal()
        ]);
        $collection2 = $collection1->merge($this->collection2->reveal());

        $this->collection1->count()->willReturn(1);
        $this->collection2->count()->willReturn(2);

        $this->assertCount(1, $collection1);
        $this->assertCount(3, $collection2);
        $this->assertNotSame($collection1, $collection2);
    }

    public function testGetsItemByName(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal()
        ]);

        $this->collection1->count()->willReturn(1);
        $this->collection2->count()->willReturn(3);

        $this->collection1->get('foobar')->willReturn($this->member1->reveal());
        $this->collection1->has('foobar')->willReturn(true);
        $this->collection1->keys()->willReturn([]);
        $this->collection2->keys()->willReturn([]);


        $item = $collection1->get('foobar');
        $this->assertSame($this->member1->reveal(), $item);
    }

    public function testThrowsExceptionIfItemDoesNotExistOnGet(): void
    {
        $this->expectException(ItemNotFound::class);

        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal()
        ]);

        $this->collection1->getIterator()->willReturn(new ArrayIterator([1]));
        $this->collection2->getIterator()->willReturn(new ArrayIterator([2,3]));

        $this->collection1->has('foobar')->willReturn(false);
        $this->collection2->has('foobar')->willReturn(false);

        $this->collection1->keys()->willReturn([]);
        $this->collection2->keys()->willReturn([]);


        $item = $collection1->get('foobar');
        $this->assertSame($this->member1->reveal(), $item);
    }

    public function testReturnsFirstItem(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal()
        ]);

        $this->collection1->first()->willReturn($this->member1->reveal());
        $this->collection1->count()->willReturn(1);

        $member = $collection1->first();
        $this->assertSame($this->member1->reveal(), $member);
    }

    public function testReturnsLastItem(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal()
        ]);

        $this->collection1->last()->willReturn($this->member1->reveal());

        $member = $collection1->last();
        $this->assertSame($this->member1->reveal(), $member);
    }

    public function testThrowsExceptionIfNoFirstItem(): void
    {
        $this->expectException(ItemNotFound::class);
        $collection1 = ChainReflectionMemberCollection::fromCollections([]);

        $collection1->first();
    }

    public function testThrowsExceptionIfNoLastItem(): void
    {
        $this->expectException(ItemNotFound::class);
        $collection1 = ChainReflectionMemberCollection::fromCollections([]);

        $collection1->last();
    }

    public function testHas(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal(),
        ]);

        $this->collection1->has('foo')->willReturn(true);
        $this->collection2->has('foo')->shouldNotBeCalled();
        $this->collection1->has('bar')->willReturn(false);
        $this->collection2->has('bar')->willReturn(false);

        $this->assertTrue($collection1->has('foo'));
        $this->assertFalse($collection1->has('bar'));
    }

    public function testReturnByVisibilities(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal(),
        ]);

        $visibilties = [ Visibility::protected() ];
        $this->collection1->byVisibilities($visibilties)->willReturn($this->collection3->reveal());
        $this->collection2->byVisibilities($visibilties)->willReturn($this->collection4->reveal());
        $this->collection1->count()->willReturn(1);
        $this->collection2->count()->willReturn(1);
        $this->collection3->count()->willReturn(1);
        $this->collection4->count()->willReturn(1);

        $collection = $collection1->byVisibilities($visibilties);
        $this->assertCount(2, $collection);
    }

    public function testReturnBelongingTo(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal(),
        ]);

        $className = ClassName::fromString('Foo');
        $this->collection1->belongingTo($className)->willReturn($this->collection3->reveal());
        $this->collection2->belongingTo($className)->willReturn($this->collection4->reveal());

        $this->assertEquals(ChainReflectionMemberCollection::fromCollections([
            $this->collection3->reveal(),
            $this->collection4->reveal()
        ]), $collection1->belongingTo($className));
    }

    public function testReturnsItemsAtOffset(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal(),
        ]);

        $name = 1;
        $this->collection1->atOffset($name)->willReturn($this->collection3->reveal());
        $this->collection2->atOffset($name)->willReturn($this->collection4->reveal());

        $this->assertEquals(ChainReflectionMemberCollection::fromCollections([
            $this->collection3->reveal(),
            $this->collection4->reveal()
        ]), $collection1->atOffset($name));
    }

    public function testReturnsMembersByName(): void
    {
        $collection1 = ChainReflectionMemberCollection::fromCollections([
            $this->collection1->reveal(),
            $this->collection2->reveal(),
        ]);

        $name = 'name';
        $this->collection1->byName($name)->willReturn($this->collection3->reveal());
        $this->collection2->byName($name)->willReturn($this->collection4->reveal());

        $this->assertEquals(ChainReflectionMemberCollection::fromCollections([
            $this->collection3->reveal(),
            $this->collection4->reveal()
        ]), $collection1->byName($name));
    }
}
