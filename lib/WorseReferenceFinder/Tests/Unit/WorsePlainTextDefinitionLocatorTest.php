<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Generator;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\WorseReferenceFinder\Tests\DefinitionLocatorTestCase;
use Phpactor\WorseReferenceFinder\WorsePlainTextClassDefinitionLocator;

class WorsePlainTextDefinitionLocatorTest extends DefinitionLocatorTestCase
{
    /**
     * @dataProvider provideGotoWord
     */
    public function testGotoWord(string $text, string $expectedPath): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar {}
            // File: Barfoo.php
            <?php namespace Barfoo { class Barfoo {} }
            // File: Boo.php
            <?php namespace Baz { class Boo {} }
            EOT
            , $text);

        $this->assertEquals($this->workspace->path($expectedPath), $location->first()->range()->uri()->path());
    }

    public function testExceptionIfCannotFindClass(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('Word "is" could not be resolved to a class');
        $this->locate('', 'Hello this i<>s ');
    }

    public function testLastOffset(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->locate('', 'Hello this is <>');
    }

    /**
     * @return Generator<mixed>
     */
    public function provideGotoWord(): Generator
    {
        yield 'property docblock' => [ '/** @var Foob<>ar */', 'Foobar.php' ];
        yield 'fully qualified' => [ '/** @var \Barfoo\Barf<>oo */', 'Barfoo.php' ];
        yield 'qualified' => [ '/** @var Barfoo\Barf<>oo */', 'Barfoo.php' ];
        yield 'xml attribute' => [ '<element class="Foob<>ar">', 'Foobar.php' ];
        yield 'array access' => [ '[Foob<>ar::class]', 'Foobar.php' ];
        yield 'list' => [ '/** @return <>Foobar[]', 'Foobar.php' ];
        yield 'solid block of text' => [ 'Foob<>ar', 'Foobar.php' ];
        yield 'imported class 1' => [ <<<'EOT'
            <?php
            namespace Bar {

            use Barfoo\Barfoo;

                class Ha {
                /** @var Ba<>rfoo */
                private $hello;
                }
            }
            EOT
        , 'Barfoo.php' ];
        yield 'imported class 2' => [ <<<'EOT'
            <?php

            use Barfoo\Barfoo;

            /** @var Ba<>rfoo */
            EOT
        , 'Barfoo.php' ];
        yield 'relative class' => [ <<<'EOT'
            <?php

            namespace Barfoo;

            /** @var Ba<>rfoo */
            EOT
        , 'Barfoo.php' ];
        yield 'imported class' => [ <<<'EOT'
            <?php

            namespace Barfoo;

            use Baz\Boo;

            /**
             * @property B<>oo
             */
            class Baz {}
            EOT
        , 'Boo.php' ];
    }

    protected function locator(): DefinitionLocator
    {
        return new WorsePlainTextClassDefinitionLocator($this->reflector());
    }
}
