<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Reflector\SourceCode;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\Reflector\SourceCode\ContextualSourceCodeReflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ContextualSourceCodeReflectorTest extends TestCase
{
    use ProphecyTrait;

    const TEST_SOURCE_CODE = 'hello';

    const TEST_OFFSET = 666;
    
    private ObjectProphecy $innerReflector;
    
    private ContextualSourceCodeReflector $reflector;
    
    private ObjectProphecy $locator;

    private $code;

    public function setUp(): void
    {
        $this->innerReflector = $this->prophesize(SourceCodeReflector::class);
        $this->locator = $this->prophesize(TemporarySourceLocator::class);

        $this->reflector = new ContextualSourceCodeReflector(
            $this->innerReflector->reveal(),
            $this->locator->reveal()
        );

        $this->code = SourceCode::fromString(self::TEST_SOURCE_CODE);
    }

    public function testReflectsClassesIn(): void
    {
        $this->locator->pushSourceCode($this->code)->shouldBeCalled();
        $this->innerReflector->reflectClassesIn($this->code)->shouldBeCalled();

        $this->reflector->reflectClassesIn(self::TEST_SOURCE_CODE);
    }

    public function testReflectOffset(): void
    {
        $this->locator->pushSourceCode($this->code)->shouldBeCalled();
        $this->innerReflector->reflectOffset(
            $this->code,
            self::TEST_OFFSET
        )->willReturn($this->prophesize(ReflectionOffset::class));

        $this->reflector->reflectOffset(self::TEST_SOURCE_CODE, self::TEST_OFFSET);
    }

    public function testReflectMethodCall(): void
    {
        $this->locator->pushSourceCode($this->code)->shouldBeCalled();
        $this->innerReflector->reflectMethodCall(
            $this->code,
            self::TEST_OFFSET
        )->willReturn($this->prophesize(ReflectionMethodCall::class));

        $this->reflector->reflectMethodCall(self::TEST_SOURCE_CODE, self::TEST_OFFSET);
    }
}
