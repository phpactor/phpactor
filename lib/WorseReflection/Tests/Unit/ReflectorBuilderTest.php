<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class ReflectorBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testBuildWithDefaults(): void
    {
        $reflector = ReflectorBuilder::create()->build();
        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testReplacesLogger(): void
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $reflector = ReflectorBuilder::create()
            ->withLogger($logger->reveal())
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testHasOneLocator(): void
    {
        $locator = $this->prophesize(SourceCodeLocator::class);
        $reflector = ReflectorBuilder::create()
            ->addLocator($locator->reveal())
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testHasManyLocators(): void
    {
        $locator = $this->prophesize(SourceCodeLocator::class);
        $reflector = ReflectorBuilder::create()
            ->addLocator($locator->reveal())
            ->addLocator($locator->reveal())
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testHighestPriorityLocatorWins(): void
    {
        $locator1 = $this->prophesize(SourceCodeLocator::class);
        $locator2 = $this->prophesize(SourceCodeLocator::class);
        $locator3 = $this->prophesize(SourceCodeLocator::class);

        $reflector = ReflectorBuilder::create()
            ->addLocator($locator1->reveal(), 0)
            ->addLocator($locator2->reveal(), 10)
            ->addLocator($locator3->reveal(), -10)
            ->build();

        $locator1->locate(Argument::any())->shouldNotBeCalled();
        $locator2->locate(Argument::any())->willReturn(TextDocument::fromString(file_get_contents(__FILE__)));
        $locator3->locate(Argument::any())->shouldNotBeCalled();

        $this->assertInstanceOf(Reflector::class, $reflector);
        $reflector->reflectClass(__CLASS__);
    }

    public function testWithSource(): void
    {
        $reflector = ReflectorBuilder::create()
            ->addSource('<?php class Foobar {}')
            ->build();

        $class = $reflector->reflectClass('Foobar');
        $this->assertEquals('Foobar', $class->name()->__toString());
        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testInternalLocatorGetsHighestPriority(): void
    {
        $reflector = ReflectorBuilder::create()
            ->addLocator(new StringSourceLocator(
                TextDocument::fromString('<?php interface BackedEnum {}')
            ), 100)
            ->build();

        $class = $reflector->reflectInterface('BackedEnum');
        $this->assertEquals('BackedEnum', $class->name()->__toString());
        $this->assertStringContainsString('InternalStubs', $class->sourceCode()->path());
    }

    public function testEnableCache(): void
    {
        $reflector = ReflectorBuilder::create()
            ->enableCache()
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testEnableContextualSourceLocation(): void
    {
        $reflector = ReflectorBuilder::create()
            ->enableContextualSourceLocation()
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }
}
