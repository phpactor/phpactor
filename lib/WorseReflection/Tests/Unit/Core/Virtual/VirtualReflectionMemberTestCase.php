<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Visibility;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

abstract class VirtualReflectionMemberTestCase extends TestCase
{
    use ProphecyTrait;

    protected ByteOffsetRange $position;

    protected ObjectProphecy $declaringClass;

    protected ObjectProphecy $class;

    protected string $name;

    protected ObjectProphecy $frame;

    protected ObjectProphecy $docblock;

    protected ObjectProphecy $scope;

    protected Visibility $visibility;

    protected Type $type;

    public function setUp(): void
    {
        $this->position = ByteOffsetRange::fromInts(0, 0);
        $this->declaringClass = $this->prophesize(ReflectionClass::class);
        $this->class = $this->prophesize(ReflectionClass::class);
        $this->name = 'test_name';
        $this->frame = $this->prophesize(Frame::class);
        $this->docblock = $this->prophesize(DocBlock::class);
        $this->scope = $this->prophesize(ReflectionScope::class);
        $this->visibility = Visibility::public();
        $this->type = TypeFactory::unknown();
    }

    abstract public function member(): ReflectionMember;

    public function testPosition(): void
    {
        $this->assertSame($this->position, $this->member()->position());
    }

    public function testDeclaringClass(): void
    {
        $this->assertSame($this->declaringClass->reveal(), $this->member()->declaringClass());
    }

    public function testClass(): void
    {
        $this->assertSame($this->class->reveal(), $this->member()->class());
    }

    public function testName(): void
    {
        $this->assertEquals($this->name, $this->member()->name());
    }

    public function testFrame(): void
    {
        $this->assertEquals($this->frame->reveal(), $this->member()->frame());
    }

    public function testDocblock(): void
    {
        $this->assertEquals($this->docblock->reveal(), $this->member()->docblock());
    }

    public function testScope(): void
    {
        $this->assertEquals($this->scope->reveal(), $this->member()->scope());
    }

    public function testVisibility(): void
    {
        $this->assertEquals($this->visibility, $this->member()->visibility());
    }

    public function testType(): void
    {
        $this->assertEquals($this->type, $this->member()->type());
    }
}
