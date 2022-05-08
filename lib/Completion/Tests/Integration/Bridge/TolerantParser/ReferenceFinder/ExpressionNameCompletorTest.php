<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\ExpressionNameCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class ExpressionNameCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @dataProvider provideComplete
     *
     * @param array<mixed> $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public function provideComplete(): Generator
    {
        yield 'new class instance' => [
            '<?php class Foobar { public function __construct(int $cparam) {} } :int {}; new Foo<>', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                    'snippet'           => 'Foobar(${1:\\$cparam})${0}',
                ]
            ]
        ];
        yield 'new class instance (empty constructor)' => [
            '<?php class Foobar { public function __construct() {} } :int {}; new Foo<>', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                    'snippet'           => 'Foobar()',
                ]
            ]
        ];
        yield 'class typehint (no instantiation)' => [
            '<?php class Foobar { public function __construct() {} } :int {}; Fo<>', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                ]
            ]
        ];

        yield 'function' => [
            '<?php function bar(int $foo) {}; ba<>', [
                [
                    'type'              => Suggestion::TYPE_FUNCTION,
                    'name'              => 'bar',
                    'short_description' => 'bar',
                    'snippet'           => 'bar(${1:\\$foo})${0}'
                ]
            ]
        ];
        yield 'function (empty params)' => [
            '<?php function bar() {}; ba<>', [
                [
                    'type'              => Suggestion::TYPE_FUNCTION,
                    'name'              => 'bar',
                    'short_description' => 'bar',
                    'snippet'           => 'bar()'
                ]
            ]
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $searcher = $this->prophesize(NameSearcher::class);
        $searcher->search('Foo')->willYield([
            NameSearchResult::create('class', 'Foobar')
        ]);
        $searcher->search('Fo')->willYield([
            NameSearchResult::create('class', 'Foobar')
        ]);
        $searcher->search('ba')->willYield([
            NameSearchResult::create('function', 'bar'),
        ]);
        $searcher->search('b')->willYield([
            NameSearchResult::create('class', 'Foo\\Bar'),
        ]);

        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        return new ExpressionNameCompletor(
            $searcher->reveal(),
            $this->snippetFormatter($reflector)
        );
    }
}
