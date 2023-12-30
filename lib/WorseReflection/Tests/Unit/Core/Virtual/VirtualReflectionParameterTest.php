<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class VirtualReflectionParameterTest extends TestCase
{
    use ProphecyTrait;

    private ByteOffsetRange $position;

    /** @var ObjectProphecy<ReflectionClass> */
    private ObjectProphecy $class;

    private string $name;

    /** @var ObjectProphecy<ReflectionScope> */
    private ObjectProphecy $scope;

    private Type $type;

    /** @var ObjectProphecy<ReflectionMethod> */
    private ObjectProphecy $method;

    private DefaultValue $defaults;

    private bool $byReference;

    public function setUp(): void
    {
        $this->position = ByteOffsetRange::fromInts(0, 0);
        $this->class = $this->prophesize(ReflectionClass::class);
        $this->name = 'test_name';
        $this->scope = $this->prophesize(ReflectionScope::class);
        $this->type = TypeFactory::unknown();
        $this->method = $this->prophesize(ReflectionMethod::class);
        $this->defaults = DefaultValue::fromValue(1234);
        $this->byReference = false;
    }

    public function parameter(): ReflectionParameter
    {
        return new VirtualReflectionParameter(
            $this->name,
            $this->method->reveal(),
            $this->type,
            $this->type,
            $this->defaults,
            $this->byReference,
            $this->scope->reveal(),
            $this->position,
            0
        );
    }

    public function testAccess(): void
    {
        $parameter = $this->parameter();
        $this->assertEquals($this->name, $parameter->name());
        $this->assertEquals($this->method->reveal(), $parameter->functionLike());
        $this->assertEquals($this->type, $parameter->inferredType());
        $this->assertEquals($this->type, $parameter->type());
        $this->assertEquals($this->defaults, $parameter->default());
        $this->assertEquals($this->byReference, $parameter->byReference());
        $this->assertEquals($this->scope->reveal(), $parameter->scope());
        $this->assertEquals($this->position, $parameter->position());
        $this->assertEquals(0, $parameter->index());
    }
}
