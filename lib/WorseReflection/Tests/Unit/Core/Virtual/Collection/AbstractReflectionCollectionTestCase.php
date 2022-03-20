<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual\Collection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

abstract class AbstractReflectionCollectionTestCase extends TestCase
{
    use ProphecyTrait;

    abstract public function collection(array $names): ReflectionCollection;

    public function testCount(): void
    {
        $this->assertEquals(2, $this->collection(['one', 'two'])->count());
    }

    public function testKeys(): void
    {
        $this->assertEquals(2, $this->collection(['one', 'two'])->count());
    }

    public function testMerge(): void
    {
        $collection = $this->collection(['one', 'two'])->merge($this->collection(['three', 'four']));
        $this->assertCount(4, $collection);
        $this->assertEquals(['one', 'two', 'three', 'four'], $collection->keys());
    }

    public function testMergeThrowsExceptionOnIncorrectType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Collection must be instance of');
        $collection = $this->prophesize(ReflectionCollection::class)->reveal();
        $this->collection([])->merge($collection);
    }

    public function testGet(): void
    {
        $this->assertNotNull($this->collection(['one'])->get('one'));
    }

    public function testGetThrowsExceptionIfItemNotExisting(): void
    {
        $this->expectException(ItemNotFound::class);
        $this->collection(['one'])->get('two');
    }

    public function testFirst(): void
    {
        $this->assertNotNull($this->collection(['one', 'two', 'three'])->first());
    }

    public function testGetFirstThrowsExceptionIfColletionIsEmpty(): void
    {
        $this->expectException(ItemNotFound::class);
        $this->collection([])->first();
    }

    public function testLast(): void
    {
        $this->assertNotNull($this->collection(['one', 'two', 'three'])->last());
    }

    public function testHas(): void
    {
        $this->assertTrue($this->collection(['one'])->has('one'));
    }
}
