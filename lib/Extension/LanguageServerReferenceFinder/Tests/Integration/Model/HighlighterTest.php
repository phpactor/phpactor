<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Integration\Model;

use Closure;
use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\TolerantHighlighter;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlights;
use Phpactor\LanguageServerProtocol\DocumentHighlightKind;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;

class HighlighterTest extends TestCase
{
    /**
     * @dataProvider provideVariables
     * @dataProvider provideProperties
     * @dataProvider provideMethods
     * @dataProvider provideNames
     * @dataProvider provideConstants
     */
    public function testHighlight(string $source, Closure $assertion): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $assertion(
            (new TolerantHighlighter(new Parser()))->highlightsFor(
                $source,
                ByteOffset::fromInt((int)$offset)
            )
        );
    }

    /**
     * @return Generator<mixed>
     */
    public function provideVariables(): Generator
    {
        yield 'none' => [
            '<?php',
            function (Highlights $highlights): void {
                self::assertCount(0, $highlights);
            }
        ];

        yield 'one' => [
            '<?php $v<>ar;',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->first()->kind);
            }
        ];

        yield 'two vars including method var' => [
            '<?php function foobar ($var) { $v<>ar; }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
            }
        ];

        yield 'only method var' => [
            '<?php function foobar ($v<>ar) {}',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
            }
        ];

        yield 'write var including method var' => [
            '<?php $var = "foo"; $v<>ar;}',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::WRITE, $highlights->first()->kind);
            }
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideProperties(): Generator
    {
        yield 'property declaration' => [
            '<?php class Foobar { private $f<>oobar; }',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];
        yield 'property declaration 2' => [
            '<?php class Foobar { private $f<>oobar; private $barfoo;}',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];
        yield 'property read' => [
            '<?php class Foobar { private $f<>oobar; function bar() { return $this->foobar; }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->at(1)->kind);
            }
        ];

        yield 'promoted property read' => [
            '<?php class Foobar { public function __construct(private $f<>oobar) {} function bar() { return $this->foobar; }',

            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->at(1)->kind);
            }
        ];

        yield 'property access' => [
            '<?php class Foobar { private $foobar; function bar() { return $this->foo<>bar->barfoo; }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->at(1)->kind);
            }
        ];

        yield 'promoted property access' => [
            '<?php class Foobar { public function __construct(private $foobar) {} function bar() { return $this->foo<>bar->barfoo; }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->at(1)->kind);
            }
        ];

        yield 'property write' => [
            '<?php class Foobar { private $f<>oobar; function bar() { return $this->foobar = "barfoo"; }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::WRITE, $highlights->at(1)->kind);
            }
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMethods(): Generator
    {
        yield 'method declaration' => [
            '<?php class Foobar { public function f<>oobar() {} }',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];

        yield 'method read' => [
            '<?php class Foobar { function bar() { return $this->b<>ar(); }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->at(1)->kind);
            }
        ];

        yield 'static method read' => [
            '<?php class Foobar { static function bar() { return self::b<>ar(); }',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
                self::assertEquals(DocumentHighlightKind::READ, $highlights->at(1)->kind);
            }
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideNames(): Generator
    {
        yield 'class name' => [
            '<?php class Foo<>bar {}',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];

        yield 'class name with fqn' => [
            '<?php class Foo<>bar {const BAR=1;} Foobar::BAR;',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];

        yield 'class in use statement' => [
            '<?php use SomeNamespace\Foo; echo SomeNamespace\Fo<>o::class;',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
            }
        ];

        yield 'class alias in use statement' => [
            '<?php use Foo as Test; echo Tes<>t::class;',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
            }
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConstants(): Generator
    {
        yield 'class constant' => [
            '<?php class Foo { const B<>AR = "";}',
            function (Highlights $highlights): void {
                self::assertCount(1, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];

        yield 'class constants' => [
            '<?php class Foo<>bar {const BAR=1;} Foobar::BAR;',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];

        yield 'class constants on reference' => [
            '<?php class Foobar {const BAR=1;} Foobar::B<>AR;',
            function (Highlights $highlights): void {
                self::assertCount(2, $highlights);
                self::assertEquals(DocumentHighlightKind::TEXT, $highlights->at(0)->kind);
            }
        ];
    }
}
