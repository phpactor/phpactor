<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;

class TemporarySourceLocatorTest extends TestCase
{
    use ProphecyTrait;

    private TemporarySourceLocator $locator;

    private Reflector $reflector;

    public function setUp(): void
    {
        $this->locator = new TemporarySourceLocator(
            ReflectorBuilder::create()->build()
        );
    }

    public function testThrowsExceptionWhenClassNotFound(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->expectExceptionMessage('Class "Foobar" not found');

        $source = TextDocumentBuilder::create('<?php class Boobar {}')->build();
        $this->locator->pushSourceCode($source);

        $this->locator->locate(ClassName::fromString('Foobar'));
    }

    public function testReturnsSourceIfClassIsInTheSource(): void
    {
        $code = '<?php class Foobar {}';

        $this->locator->pushSourceCode(TextDocumentBuilder::create($code)->build());
        $source = $this->locator->locate(ClassName::fromString('Foobar'));
        $this->assertEquals($code, (string) $source);
    }

    public function testNewFilesOverridePreviousOnes(): void
    {
        $code1 = '<?php class Foobar {}';
        $this->locator->pushSourceCode(TextDocumentBuilder::create($code1)->uri('file:///foo.php')->build());

        $code2 = '<?php class Boobar {}';
        $this->locator->pushSourceCode(TextDocumentBuilder::create($code2)->uri('file:///foo.php')->build());

        $source = $this->locator->locate(ClassName::fromString('Boobar'));
        $this->assertEquals($code2, (string) $source);
    }
}
