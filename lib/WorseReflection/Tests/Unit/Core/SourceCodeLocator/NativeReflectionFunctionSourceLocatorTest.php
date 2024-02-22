<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator\NativeReflectionFunctionSourceLocator;
use Symfony\Component\Filesystem\Path;

class NativeReflectionFunctionSourceLocatorTest extends TestCase
{
    /**
     * @var ReflectionFunctionSourceLocator
     */
    private NativeReflectionFunctionSourceLocator $locator;

    public function setUp(): void
    {
        $this->locator = new NativeReflectionFunctionSourceLocator();
    }

    public function testLocatesAFunction(): void
    {
        $location = $this->locator->locate(Name::fromString(__NAMESPACE__ . '\\test_function'));
        $this->assertEquals(Path::canonicalize(__FILE__), $location->uri()->path());
        $this->assertEquals(file_get_contents(__FILE__), $location->__toString());
    }

    public function testThrowsExceptionWhenSourceNotFound(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->locator->locate(Name::fromString(__NAMESPACE__ . '\\not_existing'));
    }

    public function testDoesNotLocateInternalFunctions(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->locator->locate(Name::fromString('assert'));
    }
}

function test_function(): void
{
}
