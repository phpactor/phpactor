<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReferenceFinder\Tests\DefinitionLocatorTestCase;
use Phpactor\WorseReferenceFinder\WorseReflectionDefinitionLocator;
use Phpactor\WorseReflection\Core\Cache\NullCache;

class WorseReflectionDefinitionLocatorTest extends DefinitionLocatorTestCase
{
    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_OFFSET = 1234;

    public function testExceptionOnNonPhpFile(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('PHP');

        $this->locator()->locateDefinition(
            TextDocumentBuilder::create('asd')->language('asd')->build(),
            ByteOffset::fromInt(1234)
        );
    }

    public function testExceptionOnUnresolvableSymbol(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('Do not know how');

        [$source, $offset] = ExtractOffset::fromSource('<?php <>');

        $this->locator()->locateDefinition(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt($offset)
        );
    }

    public function testExceptionWhenNoContainingClass(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('Containing class');

        [$source, $offset] = ExtractOffset::fromSource('<?php $foo->fo<>');

        $this->locator()->locateDefinition(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt($offset)
        );
    }

    public function testExceptionWhenContainingClassNotFound(): void
    {
        $this->markTestSkipped('Cannot reproduce');
    }

    public function testExceptionWhrenClassNoPath(): void
    {
        $this->markTestSkipped('Cannot reproduce');
    }

    public function testExceptionWhenFunctionHasNoSourceCode(): void
    {
        $this->markTestSkipped('Cannot reproduce');
    }

    public function testLocatesFunction(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: file1.php
            <?php

            function foobar()
            {
            }
            EOT
        , '<?php foob<>ar();');

        $this->assertEquals($this->workspace->path('file1.php'), (string) $location->uri()->path());
        $this->assertEquals(7, $location->offset()->toInt());
    }

    public function testExceptionForFunctionWithNoDefinition(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $location = $this->locate(<<<'EOT'
            // File: file1.php
            <?php

            function barfoo()
            {
            }
            EOT
        , '<?php foob<>ar();');
    }

    public function testExceptionIfMethodNotFound(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('Class "Foobar" has no property');
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php 

            class Foobar 
            {
            }
            EOT
        , '<?php $foo = new Foobar(); $foo->b<>ar;');
    }

    public function testLocatesToMethod(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public function bar() {} }
            EOT
        , '<?php $foo = new Foobar(); $foo->b<>ar();');

        $this->assertEquals($this->workspace->path('Foobar.php'), (string) $location->uri()->path());
        $this->assertEquals(21, $location->offset()->toInt());
    }

    public function testLocatesConstant(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { const FOOBAR = 'baz'; }
            EOT
        , '<?php Foobar::FOO<>BAR;');

        $this->assertEquals($this->workspace->path('Foobar.php'), (string) $location->uri()->path());
        $this->assertEquals(21, $location->offset()->toInt());
    }

    public function testLocatesProperty(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public $foobar; }
            EOT
        , '<?php $foo = new Foobar(); $foo->foo<>bar;');

        $this->assertEquals($this->workspace->path('Foobar.php'), $location->uri()->path());
        $this->assertEquals(21, $location->offset()->toInt());
    }

    public function testLocatesCase(): void
    {
        if (!defined('T_ENUM')) {
            $this->markTestSkipped('PHP8.1');
        }
        $location = $this->locate(<<<'EOT'
            // File: FoobarEnum.php
            <?php enum FoobarEnum { case BAR; }
            EOT
        , '<?php FoobarEnum::B<>AR;');

        $this->assertEquals($this->workspace->path('FoobarEnum.php'), $location->uri()->path());
        $this->assertEquals(24, $location->offset()->toInt());
    }

    public function testExceptionIfPropertyIsInterface(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('is an interface');
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php interface Foobar { public $foobar; }
            EOT
        , '<?php $foo = new Foobar(); $foo->foo<>bar;');

        $this->assertEquals($this->workspace->path('Foobar.php'), $location->uri()->path());
        $this->assertEquals(21, $location->offset()->toInt());
    }

    protected function locator(): DefinitionLocator
    {
        return new WorseReflectionDefinitionLocator($this->reflector(), new NullCache());
    }
}
