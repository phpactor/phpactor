<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class VirtualReflectionParameterTest extends TestCase
{
    use ProphecyTrait;

    private $position;
    
    private ObjectProphecy $class;
    
    private string $name;
    
    private ObjectProphecy $frame;
    
    private ObjectProphecy $scope;

    private $types;
    
    private Type $type;
    
    private ObjectProphecy $method;
    
    private DefaultValue $defaults;

    public function setUp(): void
    {
        $this->position = Position::fromStartAndEnd(0, 0);
        $this->class = $this->prophesize(ReflectionClass::class);
        $this->name = 'test_name';
        $this->scope = $this->prophesize(ReflectionScope::class);
        $this->types = Types::empty();
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
            $this->types,
            $this->type,
            $this->defaults,
            $this->byReference,
            $this->scope->reveal(),
            $this->position
        );
    }

    public function testAccess(): void
    {
        $parameter = $this->parameter();
        $this->assertEquals($this->name, $parameter->name());
        $this->assertEquals($this->method->reveal(), $parameter->functionLike());
        $this->assertEquals($this->method->reveal(), $parameter->method());
        $this->assertEquals($this->types, $parameter->inferredTypes());
        $this->assertEquals($this->type, $parameter->type());
        $this->assertEquals($this->defaults, $parameter->default());
        $this->assertEquals($this->byReference, $parameter->byReference());
        $this->assertEquals($this->scope->reveal(), $parameter->scope());
        $this->assertEquals($this->position, $parameter->position());
    }
}
