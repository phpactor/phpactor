<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\SourceCodeLocator;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\ReflectorBuilder;

class StubSourceLocatorTest extends IntegrationTestCase
{
    private StubSourceLocator $sourceLocator;

    public function setUp(): void
    {
        $this->workspace()->reset();

        $locator = new StringSourceLocator(TextDocumentBuilder::create('')->build());
        $reflector = ReflectorBuilder::create()->addLocator($locator)->build();
        $this->workspace()->mkdir('stubs')->mkdir('cache');

        $this->sourceLocator = new StubSourceLocator(
            $reflector,
            $this->workspace()->path('stubs'),
            $this->workspace()->path('cache')
        );
    }

    public function testCanLocateClasses(): void
    {
        $this->workspace()->put('stubs/Stub.php', '<?php class StubOne {}');
        $code = $this->sourceLocator->locate(ClassName::fromString('StubOne'));
        $this->assertStringContainsString('class StubOne', (string) $code);
    }

    public function testCanLocateFunctions(): void
    {
        $this->workspace()->put('stubs/Stub.php', '<?php function hello_world() {}');
        $code = $this->sourceLocator->locate(Name::fromString('hello_world'));
        $this->assertStringContainsString('function hello_world()', (string) $code);
    }

    public function testDoesNotParseNonPhpFiles(): void
    {
        $this->workspace()->put('stubs/Stub.xml', '<?php function hello_world() {}');
        $this->workspace()->put('stubs/Stub.php', '<?php function goodbye_world() {}');

        try {
            $code = $this->sourceLocator->locate(Name::fromString('hello_world'));
            $this->fail('Non PHP file parsed');
        } catch (NotFound) {
            $this->addToAssertionCount(1);
            return;
        }

        $code = $this->sourceLocator->locate(Name::fromString('goodbye_world'));
        $this->assertStringContainsString('function goodbye_world()', (string) $code);
    }
}
