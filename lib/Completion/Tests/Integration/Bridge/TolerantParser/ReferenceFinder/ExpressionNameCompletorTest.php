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
     * @param array{string,array<int,array<string,string>>} $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<int,array<string,string>>}>
     */
    public function provideComplete(): Generator
    {
        yield 'new class instance' => [
            '<?php class Foobar { public function __construct(int $cparam) {} } :int {}; new Foo<>',
 [
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
        yield 'absolute class typehint' => [
            '<?php class Foobar { public function __construct() {} } :int {}; \Fo<>', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'FOO',
                    'short_description' => 'FOO',
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

        yield 'constant' => [
            '<?php define("FOO", "BAR"); FO<>', [
                [
                    'type'              => Suggestion::TYPE_CONSTANT,
                    'name'              => 'FOO',
                    'short_description' => 'FOO',
                ]
            ]
        ];

        yield 'class constant inside class constant declaration' => [
            '<?php class Foobar { private const FOO; private const BAR = self::F<>  }', [
                [
                    'type'              => Suggestion::TYPE_CONSTANT,
                    'name'              => 'FOOCONST',
                    'short_description' => 'FOOCONST',
                ]
            ]
        ];

        yield 'class name inside class constant declaration' => [
            '<?php class Foobar { private const FOO; private const BAR = sel<>  }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'self',
                    'short_description' => 'self',
                ]
            ]
        ];

        yield 'nested class name inside class constant declaration' => [
            '<?php class Foobar { private const FOO; private const BAR = [ sel<>  }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'self',
                    'short_description' => 'self',
                ]
            ]
        ];

        yield 'class name inside the first match arm' => [
            '<?php class Foobar {} match (true) { Fo<> }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                ]
            ],
        ];

        yield 'class name inside the second match arm' => [
            '<?php class Foobar {} match (true) { 1, Fo<> }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                ]
            ],
        ];

        yield 'class name inside match expression' => [
            '<?php class Foobar {} match (true) { 1 => Fo<> }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                ]
            ],
        ];

        yield 'within namespace with no import match' => [
            '<?php namespace NS1{class Foobar {} match (true) { 1 => Foo\Fo<> }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                ]
            ],
        ];

        yield 'within namespace with with import match' => [
            '<?php namespace NS1{ use Foobar\Foo; class Foobar {} match (true) { 1 => Foo\Fo<> }', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar\Foo\Foobar',
                ]
            ],
        ];

        yield 'only show children for qualified names' => [
            '<?php namespace NS1{ use Foobar\Foo; class Foobar {} match (true) { 1 => Relative\<> }', [
                [
                    'type'              => Suggestion::TYPE_MODULE,
                    'name'              => 'One',
                    'short_description' => 'NS1\Relative\One',
                ],
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Two',
                    'short_description' => 'NS1\Relative\Two',
                ],
                [
                    'type'              => Suggestion::TYPE_MODULE,
                    'name'              => 'Two',
                    'short_description' => 'NS1\Relative\Two',
                ],
            ],
        ];
        yield 'bare call' => [
            '<?php class Foobar {public function bar(Baz $b) {}} $f = new Foobar; $f->bar(<>', [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'EmptyString',
                    'short_description' => 'EmptyString',
                ],
            ],
        ];
        yield 'php tag' => [
            '<?ph<>', [
            ],
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $searcher = $this->prophesize(NameSearcher::class);
        $searcher->search('FO', null)->willYield([
            NameSearchResult::create('constant', 'FOO')
        ]);
        $searcher->search('ph', null)->willYield([
            NameSearchResult::create('constant', 'FOO')
        ]);
        $searcher->search('sel', null)->willYield([
            NameSearchResult::create('class', 'self')
        ]);
        $searcher->search('F', null)->willYield([
            NameSearchResult::create('constant', 'FOOCONST')
        ]);
        $searcher->search('\Fo', null)->willYield([
            NameSearchResult::create('class', 'FOO')
        ]);
        $searcher->search('Foo', null)->willYield([
            NameSearchResult::create('class', 'Foobar')
        ]);
        $searcher->search('Fo', null)->willYield([
            NameSearchResult::create('class', 'Foobar')
        ]);
        $searcher->search('ba', null)->willYield([
            NameSearchResult::create('function', 'bar'),
        ]);
        $searcher->search('b', null)->willYield([
            NameSearchResult::create('class', 'Foo\\Bar'),
        ]);
        $searcher->search('', null)->willYield([
            NameSearchResult::create('class', 'EmptyString'),
        ]);
        $searcher->search('\NS1\Foo\Fo', null)->willYield([
            NameSearchResult::create('class', 'Foobar')
        ]);
        $searcher->search('\Foobar\Foo\Fo', null)->willYield([
            NameSearchResult::create('class', 'Foobar\Foo\Foobar')
        ]);
        $searcher->search('\\NS1\\Relative', null)->willYield([
            NameSearchResult::create('class', 'NS1\Relative\One\Blah\Boo'),
            NameSearchResult::create('class', 'NS1\Relative\One\Glorm\Bar'),
            NameSearchResult::create('class', 'NS1\Relative\One\Blah'),
            NameSearchResult::create('class', 'NS1\Relative\Two'),
            NameSearchResult::create('class', 'NS1\Relative\Two\Glorm\Bar'),
        ]);

        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        return new ExpressionNameCompletor(
            $searcher->reveal(),
            $this->snippetFormatter($reflector)
        );
    }
}
