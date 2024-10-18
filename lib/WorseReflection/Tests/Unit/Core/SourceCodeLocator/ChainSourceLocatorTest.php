<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Prophecy\PhpUnit\ProphecyTrait;

class ChainSourceLocatorTest extends TestCase
{
    use ProphecyTrait;

    private $locator1;

    private $locator2;

    private $chain;

    public function setUp(): void
    {
        $this->locator1 = $this->prophesize(SourceCodeLocator::class);
        $this->locator2 = $this->prophesize(SourceCodeLocator::class);
    }

    /**
     * @testdox It throws an exception if no loaders found.
     */
    public function testNoLocators(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->locate([], ClassName::fromString('as'));
    }

    /**
     * @testdox It delegates to first loader.
     */
    public function testDelegateToFirst(): void
    {
        $expectedSource = TextDocumentBuilder::create('hello')->build();
        $class = ClassName::fromString('Foobar');
        $this->locator1->locate($class)->willReturn($expectedSource);
        $this->locator2->locate($class)->shouldNotBeCalled();

        $source = $this->locate([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ], $class);

        $this->assertSame($expectedSource, $source);
    }

    /**
     * @testdox It delegates to second if first throws exception.
     */
    public function testDelegateToSecond(): void
    {
        $expectedSource = TextDocumentBuilder::create('hello')->build();
        $class = ClassName::fromString('Foobar');
        $this->locator1->locate($class)->willThrow(new SourceNotFound('Foo'));
        $this->locator2->locate($class)->willReturn($expectedSource);

        $source = $this->locate([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ], $class);

        $this->assertSame($expectedSource, $source);
    }

    /**
     * @testdox It throws an exception if all fail
     */
    public function testAllFail(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->expectExceptionMessage('Could not find source with "Foobar"');
        $expectedSource = TextDocumentBuilder::create('hello')->build();
        $class = ClassName::fromString('Foobar');
        $this->locator1->locate($class)->willThrow(new SourceNotFound('Foo'));
        $this->locator2->locate($class)->willThrow(new SourceNotFound('Foo'));

        $this->locate([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ], $class);
    }

    private function locate(array $locators, ClassName $className)
    {
        $locator = new ChainSourceLocator($locators);
        return $locator->locate($className);
    }
}
