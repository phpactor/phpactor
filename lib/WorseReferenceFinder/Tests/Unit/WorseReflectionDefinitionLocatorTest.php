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
        $this->expectExceptionMessage('No definition(s) found');

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

        $this->assertTypeLocation($location->first(), 'file1.php', 7, 28);
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
        $this->expectExceptionMessage('No definition(s) found');
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

        $locationRange = $location->first()->location();

        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 45);
    }

    public function testLocatesToConstant(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php define('FOOBAR', 123); }
            EOT
            , '<?php FOO<>BAR;');

        $locationRange = $location->first()->location();
        $this->assertEquals($this->workspace->path('Foobar.php'), (string) $locationRange->uri()->path());
    }

    public function testLocatesMethodDeclaration(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            EOT
            , '<?php class Foobar { public function b<>ar() {} }');

        $this->assertTypeLocation($location->first(), 'somefile.php', 21, 45);
    }

    public function testLocatesMethodDeclarationInParentClass(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php abstract class Foobar { abstract public function bar() {} }
            // File: Barfoo.php
            <?php class Barfoo extends Foobar { public function b<>ar() {} }
            EOT
            , '<?php class Barfoo extends Foobar { public function b<>ar() {} }');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 30, 63);
    }

    public function testLocatesPropertyInParentClass(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public $bar; }
            // File: Barfoo.php
            <?php class Barfoo extends Foobar { public string $bar; }
            EOT
            , '<?php class Barfoo extends Foobar { public string $b<>ar; }');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 33);
    }

    public function testLocatesMethodInInterface(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php interface Foobar { public function foo(); }
            // File: Barfoo.php
            <?php class Barfoo implements Foobar { public function foo() {} }
            EOT
            , '<?php class Barfoo implements Foobar { public function f<>oo() }');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 25, 47);
    }

    public function testLocatesToMethodOnUnionTypeWithOneTypeMissingTheMethod(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Factory.php
            <?php class Factory { public static function create(): Barfoo|Foobar {} }
            // File: Foobar.php
            <?php class Foobar { public function bar() {} }
            // File: Barfoo.php
            <?php class Barfoo { }
            EOT
            , '<?php $f = Factory::create(); $f->b<>ar();');

        self::assertCount(1, $location);
        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 45);
    }

    public function testLocatesToMethodOnUnionTypeFromParam(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public function bar() {} }
            // File: Barfoo.php
            <?php class Barfoo { public function bar() {} }
            EOT
            , '<?php class F { function foo(Foobar|Barfoo $bar) { $bar->b<>ar(); }}');

        self::assertCount(2, $location);
        self::assertEquals('Foobar', $location->atIndex(0)->type()->__toString());
        self::assertEquals('Barfoo', $location->atIndex(1)->type()->__toString());
    }

    public function testLocatesToMethodOnUnionType(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Factory.php
            <?php class Factory { public static function create(): Foobar|Barfoo {} }
            // File: Foobar.php
            <?php class Foobar { public function bar() {} }
            // File: Barfoo.php
            <?php class Barfoo { public function bar() {} }
            EOT
            , '<?php $f = Factory::create(); $f->b<>ar();');

        self::assertCount(2, $location);

        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 45);
    }

    public function testLocatesConstant(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { const FOOBAR = 'baz'; }
            EOT
            , '<?php Foobar::FOO<>BAR;');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 42);
    }

    public function testLocatesProperty(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public $foobar; }
            EOT
            , '<?php $foo = new Foobar(); $foo->foo<>bar;');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 36);
    }

    public function testLocatesGeneric(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php /** @template T */class Foobar { public function get() }
            // File: Barfoo.php
            <?php class Barfoo { /** @return Foobar<Barfoo> */public static function barfoo() {} }
            EOT
            , '<?php $f = Barfoo::barfoo(); $f->g<>et();');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 39, 62);
    }

    public function testLocatesDeclaringClass(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar { public function barfoo() {} }
            // File: Barfoo.php
            <?php class Barfoo extends Foobar {}
            EOT
            , '<?php $bar = new Barfoo(); $bar->bar<>foo();');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 48);
    }

    public function testLocatesNullableMethod(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar {public function baz(){}}
            // File: Barfoo.php
            <?php class Barfoo{public function foobar(): ?Foobar}
            EOT
            , '<?php $bar = new Barfoo(); $bar->foobar()->baz<>();');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 20, 43);
    }

    public function testLocatesNullableProperty(): void
    {
        $location = $this->locate(<<<'EOT'
            // File: Foobar.php
            <?php class Foobar {public $baz;}
            // File: Barfoo.php
            <?php class Barfoo{/** @var ?Foobar */public $foobar}
            EOT
            , '<?php $bar = new Barfoo(); $bar->foobar->baz<>;');

        $this->assertTypeLocation($location->first(), 'Foobar.php', 20, 32);
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

        $this->assertTypeLocation($location->first(), 'FoobarEnum.php', 24, 33);
    }

    public function testLocatesEnumConst(): void
    {
        if (!defined('T_ENUM')) {
            $this->markTestSkipped('PHP8.1');
        }
        $location = $this->locate(<<<'EOT'
            // File: FoobarEnum.php
            <?php enum FoobarEnum { case BAR; const FOOBAR = 'FOOBAR'; }
            EOT
            , '<?php FoobarEnum::FOO<>BAR;');

        $this->assertTypeLocation($location->first(), 'FoobarEnum.php', 34, 58);
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

        $locationRange = $location->first()->location();
        $this->assertTypeLocation($location->first(), 'Foobar.php', 21, 24);
    }

    protected function locator(): DefinitionLocator
    {
        return new WorseReflectionDefinitionLocator($this->reflector(), new NullCache());
    }
}
