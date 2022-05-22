<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Prophecy\PhpUnit\ProphecyTrait;

class TemporarySourceLocatorTest extends TestCase
{
    use ProphecyTrait;
    
    private TemporarySourceLocator $locator;

    public function setUp(): void
    {
        $this->reflector = $this->prophesize(SourceCodeReflector::class);
        $this->locator = new TemporarySourceLocator(
            $this->reflector->reveal()
        );

        $this->classCollection = $this->prophesize(ReflectionClassCollection::class);
    }

    public function testThrowsExceptionWhenClassNotFound(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->expectExceptionMessage('Class "Foobar" not found');

        $source = SourceCode::fromString('<?php class Boobar {}');

        $this->reflector->reflectClassesIn($source)->willReturn(
            $this->classCollection->reveal()
        );
        $this->classCollection->has('Foobar')->willReturn(false);

        $this->locator->pushSourceCode($source);

        $this->locator->locate(ClassName::fromString('Foobar'));
    }

    public function testReturnsSourceIfClassIsInTheSource(): void
    {
        $code = 'class Foobar {}';

        $this->reflector->reflectClassesIn($code)->willReturn(
            $this->classCollection->reveal()
        );
        $this->classCollection->has('Foobar')->willReturn(true);

        $this->locator->pushSourceCode(SourceCode::fromString($code));
        $source = $this->locator->locate(ClassName::fromString('Foobar'));
        $this->assertEquals($code, (string) $source);
    }

    public function testNewFilesOverridePreviousOnes(): void
    {
        $code1 = 'class Foobar {}';
        $this->locator->pushSourceCode(SourceCode::fromPathAndString('foo.php', $code1));

        $code2 = 'class Boobar {}';
        $this->locator->pushSourceCode(SourceCode::fromPathAndString('foo.php', $code2));

        $this->reflector->reflectClassesIn(SourceCode::fromPathAndString('foo.php', $code2))->willReturn(
            $this->classCollection->reveal()
        );
        $this->classCollection->has('Boobar')->willReturn(true);

        $source = $this->locator->locate(ClassName::fromString('Boobar'));
        $this->assertEquals($code2, (string) $source);
    }
}
