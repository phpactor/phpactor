<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\InlayHint;

use Closure;
use Generator;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintOptions;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintProvider;
use Phpactor\Extension\LanguageServerWorseReflection\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class InlayHintProviderTest extends IntegrationTestCase
{
    /**
     * @param Closure(iterable<InlayHint>): void $assertion
     * @dataProvider provideInlayHintProvider
     */
    public function testInlayHintProvider(
        string $source,
        Closure $assertion
    ): void {
        $hints = wait((new InlayHintProvider(
            ReflectorBuilder::create()->addSource($source)->build(),
            new InlayHintOptions(true, true),
        ))->inlayHints(
            TextDocumentBuilder::create(
                $source
            )->build(),
            ByteOffsetRange::fromInts(
                0,
                strlen($source)
            ),
        ));
        $assertion($hints);
    }

    /**
     * @return Generator<string,array{string,Closure(list<InlayHint>): void}>
     */
    public function provideInlayHintProvider(): Generator
    {
        yield 'inlay hint for member' => [
            '<?php class Foo{ function bar(string $bar): void {}} (new Foo())->bar("hello");',
            function (array $hints): void {
                self::assertCount(3, $hints);
                $hint = $hints[2];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals('bar:', $hint->label);
                self::assertEquals('string', $hint->tooltip);
            }
        ];
        yield 'inlay hint for variable only definitions' => [
            '<?php $foo = "foo"; $foo;',
            function (array $hints): void {
                self::assertCount(1, $hints);
                $hint = $hints[0];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals(': string', $hint->label);
                self::assertEquals('"foo"', $hint->tooltip);
            }
        ];
        yield 'inlay hint for variable only definitions 2' => [
            '<?php $foo = "foo"; $foo->bar();',
            function (array $hints): void {
                self::assertCount(1, $hints);
                $hint = $hints[0];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals(': string', $hint->label);
                self::assertEquals('"foo"', $hint->tooltip);
            }
        ];
        yield 'inlay hint for variable only definitions 3' => [
            '<?php class Fo {function bar():string {}}; $foo = new Fo(); $foo->bar(); $bar = $foo->bar();',
            function (array $hints): void {
                self::assertCount(4, $hints);
                $hint = $hints[2];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals(': Fo', $hint->label);
            }
        ];
        yield 'inlay hint for variable only definitions 4' => [
            '<?php class Fo {function bar():string { if ($this->bar() == "bar") {}; }};',
            function (array $hints): void {
                self::assertCount(2, $hints);
            }
        ];
        yield 'inlay hint for class instantiation' => [
            '<?php class A { function __construct($a, $b) {}} new A(1, 2)',
            function (array $hints): void {
                self::assertCount(4, $hints);
                $hint = $hints[3];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals('b:', $hint->label);
            }
        ];
        yield 'inlay hint for function call' => [
            '<?php function foobar($a, $b) {} foobar(1, 2);',
            function (array $hints): void {
                self::assertCount(3, $hints);
                $hint = $hints[2];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals('b:', $hint->label);
            }
        ];
        yield 'inlay hint for function call ignore named' => [
            '<?php function foobar($a, $b) {} foobar(1, b: 2);',
            function (array $hints): void {
                self::assertCount(2, $hints);
            }
        ];
        yield 'inlay hint for function call ignore if variable name same' => [
            '<?php function foobar($a, $b) {} foobar(1, $b);',
            function (array $hints): void {
                self::assertCount(2, $hints);
            }
        ];
        yield 'inlay hint for stub function' => [
            '<?php function array_map($func, $arr){}array_map("trim", []);',
            function (array $hints): void {
                self::assertCount(3, $hints);
                $hint = $hints[2];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals('arr:', $hint->label);
            }
        ];
        yield 'inlay hint for class' => [
            '<?php class Foobar {}',
            function (array $hints): void {
                self::assertCount(1, $hints);
                $hint = $hints[0];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals('class Foobar', $hint->label);
            }
        ];
        yield 'inlay hint for method' => [
            '<?php class Foobar { public function bar(): string {}}',
            function (array $hints): void {
                self::assertCount(2, $hints);
                $hint = $hints[1];
                assert($hint instanceof InlayHint);
                self::assertEquals(0, $hint->position->line);
                self::assertEquals('class Foobar', $hint->label);
            }
        ];
    }
}
