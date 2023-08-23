<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Microsoft\PhpParser\Parser;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\WorseReferenceFinder\Tests\DefinitionLocatorTestCase;
use Phpactor\WorseReferenceFinder\TolerantVariableDefintionLocator;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;

class TolerantVariableDefintionLocatorTest extends DefinitionLocatorTestCase
{
    public function testLocatesLocalVariable(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public $foobar; }
            EOT
            , '<?php $foo = new Foobar(); $f<>oo->foobar;');

        $this->assertTypeLocation($location->first(), 'somefile.php', 6, 10);
    }

    public function testVariableIsMethodArgument(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public $foobar; }
            EOT
            , '<?php class Foo { public function bar(string $bar) { $b<>ar->baz(); } }');

        $this->assertTypeLocation($location->first(), 'somefile.php', 45, 50);
    }

    public function testGotoFirstIfVariableNotDefined(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public $foobar; }
            EOT
            , '<?php $foo = new Foobar(); $b<>ar->foobar;');

        $this->assertTypeLocation($location->first(), 'somefile.php', 27, 31);
    }

    protected function locator(): DefinitionLocator
    {
        return new TolerantVariableDefintionLocator(
            new TolerantVariableReferenceFinder(new Parser(), true)
        );
    }
}
