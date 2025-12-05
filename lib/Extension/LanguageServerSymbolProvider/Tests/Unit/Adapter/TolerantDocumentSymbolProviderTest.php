<?php

namespace Phpactor\Extension\LanguageServerSymbolProvider\Tests\Unit\Adapter;

use Exception;
use Generator;
use Phpactor\LanguageServerProtocol\DocumentSymbol;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\SymbolKind;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerSymbolProvider\Adapter\TolerantDocumentSymbolProvider;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\Framework\ExpectationFailedException;

class TolerantDocumentSymbolProviderTest extends TestCase
{
    const DUMMY_RANGE = 10000;

    /**
     * @dataProvider provideClasses
     * @dataProvider provideInterfaces
     * @dataProvider provideTraits
     * @dataProvider provideFunctions
     * @dataProvider provideEnums
     *
     * @param DocumentSymbol[] $expected
     * @throws Exception
     * @throws FrameworkException
     * @throws ExpectationFailedException
     */
    public function testBuildDocumentSymbol(string $source, array $expected): void
    {
        $actual = (new TolerantDocumentSymbolProvider(new Parser()))->provideFor($source);
        $this->assertTree($actual, $expected);
    }

    public function provideFunctions(): Generator
    {
        yield 'functions' => [
            '<?php function bar {}',
            [
                new DocumentSymbol(
                    'bar',
                    SymbolKind::FUNCTION,
                    $this->dummyRange(),
                    $this->dummyRange(),
                    null,
                    null,
                    children: []
                ),
            ]
        ];
    }

    public function provideClasses(): Generator
    {
        yield 'class' => [
            '<?php class Foo {}',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::CLASS_,
                    new Range(new Position(0, 6), new Position(0, 18)),
                    new Range(new Position(0, 12), new Position(0, 15)),
                    null,
                    null,
                    children: []
                ),
            ]
        ];

        yield 'class method' => [
            '<?php class Foo { public function bar() {}}',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::CLASS_,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => 'bar',
                            'kind' => SymbolKind::METHOD,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];

        yield 'class construct' => [
            '<?php class Foo { public function __construct() {}}',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::CLASS_,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => '__construct',
                            'kind' => SymbolKind::CONSTRUCTOR,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];

        yield 'class property with value' => [
            '<?php class Foo { private $bar = "Hallo"; }',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::CLASS_,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => 'bar',
                            'kind' => SymbolKind::PROPERTY,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];

        yield 'class property' => [
            '<?php class Foo { private $bar; }',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::CLASS_,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => 'bar',
                            'kind' => SymbolKind::PROPERTY,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];

        yield 'class constant' => [
            '<?php class Foo { const BAR="foo"; }',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::CLASS_,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => 'BAR',
                            'kind' => SymbolKind::CONSTANT,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];
    }

    public function provideInterfaces(): Generator
    {
        yield 'interface' => [
            '<?php interface Foo {}',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::INTERFACE,
                    new Range(new Position(0, 6), new Position(0, 22)),
                    new Range(new Position(0, 16), new Position(0, 19)),
                    null,
                    null,
                    children: []
                ),
            ]
        ];

        yield 'interface method' => [
            '<?php interface Foo { public function bar() {}}',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::INTERFACE,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => 'bar',
                            'kind' => SymbolKind::METHOD,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];

        yield 'interface constant' => [
            '<?php interface Foo { const BAR="foo"; {}}',
            [
                DocumentSymbol::fromArray([
                    'name' => 'Foo',
                    'kind' => SymbolKind::INTERFACE,
                    'range' => $this->dummyRange(),
                    'selectionRange' => $this->dummyRange(),
                    'children' => [
                        DocumentSymbol::fromArray([
                            'name' => 'BAR',
                            'kind' => SymbolKind::CONSTANT,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ])
            ]
        ];
    }

    public function provideTraits(): Generator
    {
        yield 'trait' => [
            '<?php trait Foo {}',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::CLASS_,
                    $this->dummyRange(),
                    $this->dummyRange(),
                    null,
                    null,
                    children: []
                ),
            ]
        ];

        yield 'property in trait' => [
            '<?php trait Foo { public $foo; }',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::CLASS_,
                    $this->dummyRange(),
                    $this->dummyRange(),
                    null,
                    null,
                    children: [
                        DocumentSymbol::fromArray([
                            'name' => 'foo',
                            'kind' => SymbolKind::PROPERTY,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ),
            ]
        ];

        yield 'method in trait' => [
            '<?php trait Foo { public function foo() {} }',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::CLASS_,
                    $this->dummyRange(),
                    $this->dummyRange(),
                    null,
                    null,
                    children: [
                        DocumentSymbol::fromArray([
                            'name' => 'foo',
                            'kind' => SymbolKind::METHOD,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ),
            ]
        ];
    }

    public function provideEnums(): Generator
    {
        yield 'enum' => [
            '<?php enum Foo {}',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::ENUM,
                    $this->dummyRange(),
                    $this->dummyRange(),
                    null,
                    null,
                    children: []
                ),
            ]
        ];

        yield 'members of enum' => [
            '<?php enum Foo { case BAR; case SPAM; }',
            [
                new DocumentSymbol(
                    'Foo',
                    SymbolKind::ENUM,
                    $this->dummyRange(),
                    $this->dummyRange(),
                    null,
                    null,
                    children: [
                        DocumentSymbol::fromArray([
                            'name' => 'BAR',
                            'kind' => SymbolKind::ENUM_MEMBER,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                        DocumentSymbol::fromArray([
                            'name' => 'SPAM',
                            'kind' => SymbolKind::ENUM_MEMBER,
                            'range' => $this->dummyRange(),
                            'selectionRange' => $this->dummyRange(),
                            'children' => [],
                        ]),
                    ]
                ),
            ]
        ];
    }

    private function dummyRange(): Range
    {
        return ProtocolFactory::range(0, 0, self::DUMMY_RANGE, 0);
    }

    /**
     * @param DocumentSymbol[] $actual
     * @param DocumentSymbol[] $expected
     * @throws FrameworkException
     * @throws ExpectationFailedException
     */
    private function assertTree(array $actual, array $expected): void
    {
        self::assertCount(count($expected), $actual, 'Expected number of children');

        foreach ($actual as $index => $symbol) {
            assert($symbol instanceof DocumentSymbol);
            $expectedSymbol = $expected[$index];
            self::assertNotNull($expected, 'Missing document symbol');
            assert($expectedSymbol instanceof DocumentSymbol);

            if ($expectedSymbol->range->end->line === self::DUMMY_RANGE) {
                $expectedSymbol->range = $symbol->range;
            }
            if ($expectedSymbol->selectionRange->end->line === self::DUMMY_RANGE) {
                $expectedSymbol->selectionRange = $symbol->selectionRange;
            }

            $actualChildren = $symbol->children;
            $symbol->children = [];
            $expectedChildren = $expectedSymbol->children;
            $expectedSymbol->children = [];

            self::assertEquals($expectedSymbol, $symbol);
            if (null !== $actualChildren) {
                self::assertNotNull($expectedChildren);
                if (null !== $expectedChildren) {
                    $this->assertTree($actualChildren, $expectedChildren);
                }
            } else {
                self::assertNull($expectedChildren);
            }
        }
    }
}
