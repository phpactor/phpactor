<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\ReferenceFinder;

use Closure;
use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\ExpressionNameCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\Suggestions;
use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\ReferenceFinder\Search\PredefinedNameSearcher;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class ExpressionNameCompletorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideComplete
     *
     * @param array<int,NameSearchResult> $searchResults
     * @param Closure(Suggestions): void $assertion
     */
    public function testComplete(array $searchResults, string $source, Closure $assertion): void
    {
        $searcher = new PredefinedNameSearcher($searchResults);

        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        $completor = new ExpressionNameCompletor(
            $searcher,
            $this->snippetFormatter($reflector)
        );

        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition($offset);
        $results = new Suggestions(
            ...iterator_to_array(
                $completor->complete(
                    $node,
                    TextDocumentBuilder::fromUnknown($source),
                    ByteOffset::fromInt($offset)
                ),
                false
            )
        );
        $assertion($results);
    }

    /**
     * @return Generator<string,array{list<NameSearchResult>,string,Closure(Suggestions):void}>
     */
    public function provideComplete(): Generator
    {
        yield 'new class instance' => [
            [
                NameSearchResult::create('class', 'Foobar'),
            ],
            '<?php class Foobar { public function __construct(int $cparam) {} } :int {}; new Foo<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_CLASS, $suggestions->at(0)->type());
                self::assertEquals('Foobar', $suggestions->at(0)->name());
                self::assertEquals('Foobar', $suggestions->at(0)->shortDescription());
                self::assertEquals('Foobar(${1:\\$cparam})${0}', $suggestions->at(0)->snippet());
            }
        ];
        yield 'new class instance (empty constructor)' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar { public function __construct() {} } :int {}; new Foo<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_CLASS, $suggestions->at(0)->type());
                self::assertEquals('Foobar', $suggestions->at(0)->name());
                self::assertEquals('Foobar', $suggestions->at(0)->shortDescription());
                self::assertEquals('Foobar()', $suggestions->at(0)->snippet());
            }
        ];
        yield 'class typehint (no instantiation)' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar { public function __construct() {} } :int {}; Fo<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_CLASS, $suggestions->at(0)->type());
                self::assertEquals('Foobar', $suggestions->at(0)->name());
                self::assertEquals('Foobar', $suggestions->at(0)->shortDescription());
            }
        ];

        yield 'function' => [
            [
                NameSearchResult::create('class', 'Bar'),
                NameSearchResult::create('function', 'bar_foo'),
            ],
            '<?php function bar_foo(int $foo) {}; ba<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_FUNCTION, $suggestions->at(0)->type());
                self::assertEquals('bar_foo', $suggestions->at(0)->name());
                self::assertEquals('bar_foo', $suggestions->at(0)->shortDescription());
                self::assertEquals('bar_foo(${1:\\$foo})${0}', $suggestions->at(0)->snippet());
            }
        ];
        yield 'function (empty params)' => [
            [
                NameSearchResult::create('function', 'bar'),
            ],
            '<?php function bar() {}; ba<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_FUNCTION, $suggestions->at(0)->type());
                self::assertEquals('bar', $suggestions->at(0)->name());
                self::assertEquals('bar', $suggestions->at(0)->shortDescription());
                self::assertEquals('bar()', $suggestions->at(0)->snippet());
            }
        ];

        yield 'constant' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('constant', 'FOO'),
            ],
            '<?php define("FOO", "BAR"); FO<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_CONSTANT, $suggestions->at(0)->type());
                self::assertEquals('FOO', $suggestions->at(0)->name());
            },
        ];

        yield 'class constant inside class constant declaration' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
                NameSearchResult::create('class', 'FOOBAR'),
            ],
            '<?php class Foobar { private const FOO = 1; private const BAR = self::F<>  }',
            function (Suggestions $suggestions): void {
                self::assertCount(0, $suggestions);
            }
        ];

        yield 'class name inside class constant declaration' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
                NameSearchResult::create('constant', 'FOO'),
            ],
            '<?php class Foobar { private const FOO = 1; private const BAR = FOO<>  }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_CONSTANT, $suggestions->at(0)->type());
                self::assertEquals('FOO', $suggestions->at(0)->name());
            }
        ];

        yield 'class name inside heredoc' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('constant', 'FOO'),
            ],
            '<?php $x = <<<F<>',
            function (Suggestions $suggestions): void {
                self::assertCount(0, $suggestions);
            }
        ];

        yield 'nested class name inside class constant declaration' => [
            [
                NameSearchResult::create('constant', 'FOO'),
            ],
            '<?php class Foobar { private const FOO = 1; private const BAR = [ FOO<>  }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals(Suggestion::TYPE_CONSTANT, $suggestions->at(0)->type());
            }
        ];

        yield 'class name inside the first match arm' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar {} match (true) { Fo<> }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            }
        ];

        yield 'class name inside the second match arm' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar {} match (true) { 1, Fo<> }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            }
        ];

        yield 'class name inside match expression' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar {} match (true) { 1 => Fo<> }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            }
        ];

        yield 'within namespace with no import match' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'NS1\Foo\Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php namespace NS1{class Foobar {} match (true) { 1 => Foo\Fo<> }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals('Foobar', $suggestions->at(0)->name());
            }
        ];

        yield 'within namespace with with import match' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'NS1\Foo\Foobar'),
                NameSearchResult::create('class', 'Foobar\Foo\Foo'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php namespace NS1{ use Foobar\Foo; class Foobar {} match (true) { 1 => Foo\Fo<> }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
                self::assertEquals('Foo', $suggestions->at(0)->name());
            }
        ];

        yield 'only show children for qualified names' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
                NameSearchResult::create('class', 'NS1\Relative\One\Blah\Boo'),
                NameSearchResult::create('class', 'NS1\Relative\One\Glorm\Bar'),
                NameSearchResult::create('class', 'NS1\Relative\One\Blah'),
                NameSearchResult::create('class', 'NS1\Relative\Two'),
                NameSearchResult::create('class', 'NS1\Relative\Two\Glorm\Bar'),
            ],
            '<?php namespace NS1{ use Foobar\Foo; class Foobar {} match (true) { 1 => Relative\<> }',
            function (Suggestions $suggestions): void {
                self::assertCount(3, $suggestions);
                self::assertEquals('One', $suggestions->at(0)->name());
                self::assertEquals('Two', $suggestions->at(1)->name());
                self::assertEquals('Two', $suggestions->at(2)->name());
                self::assertEquals(Suggestion::TYPE_MODULE, $suggestions->at(2)->type());
            }
        ];
        yield 'bare call' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar {public function bar(Baz $b) {}} $f = new Foobar; $f->bar(<>',
            function (Suggestions $suggestions): void {
                self::assertCount(2, $suggestions);
            }
        ];
        yield 'bare call 2' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php class Foobar {public function bar(Baz $b) {}} $f = new Foobar; $f->bar(F<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            }
        ];
        yield 'php tag' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?ph<>',
            function (Suggestions $suggestions): void {
                self::assertCount(0, $suggestions);
            }
        ];
        yield 'after php tag' => [
            [
                NameSearchResult::create('class', 'Foobar'),
                NameSearchResult::create('class', 'Class'),
            ],
            '<?php F<>',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            }
        ];
        yield 'attribute parameter value outside class' => [
            [
                NameSearchResult::create('class', 'Xxyz'),
            ],
            '<?php class Xxyz {} #[Attribute(flags: Xxy<>)] function foo() {}',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            },
        ];
        yield 'attribute parameter value - property' => [
            [
                NameSearchResult::create('class', 'Xxyz'),
            ],
            '<?php class Xxyz {} class Foobar { #[Attribute(flags: Xxy<>)] public x; }',
            function (Suggestions $suggestions): void {
                self::assertCount(1, $suggestions);
            },
        ];
    }
}
