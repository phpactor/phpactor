<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\ObjectRenderer\ObjectRendererBuilder;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\StaticCache;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseClassMemberCompletor;
use Generator;

/** @phpstan-import-type SuggestionOptions from Suggestion */
class WorseClassMemberCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @dataProvider provideComplete
     * @param array<SuggestionOptions> $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<SuggestionOptions>}>
     */
    public function provideComplete(): Generator
    {
        yield 'Public property' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public $foo;
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => 'foo',
                    'short_description' => 'pub $foo',
                ]
            ]
        ];

        yield 'Private property' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    private $foo;
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            ,
            [ ]
        ];

        yield 'Public property access' => [
            <<<'EOT'
                <?php

                class Barar
                {
                    public $bar;
                }

                class Foobar
                {
                    /**
                        * @var Barar
                        */
                    public $foo;
                }

                $foobar = new Foobar();
                $foobar->foo-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => 'bar',
                    'short_description' => 'pub $bar',
                ]
            ]
        ];

        yield 'Public method with parameters' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function foo(string $zzzbar = 'bar', $def): Barbar
                    {
                    }
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo(string $zzzbar = \'bar\', $def): Barbar',
                    'snippet' => 'foo(${1:\$def})${0}',
                ]
            ]
        ];

        yield 'Public method multiple return types' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                    * @return Foobar|Barbar
                    */
                    public function foo()
                    {
                    }
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo(): Foobar|Barbar',
                    'snippet' => 'foo()',
                ]
            ]
        ];

        yield 'Private method' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    private function foo(): Barbar
                    {
                    }
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
            ]
        ];

        yield 'Public method with documentation' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * Returns a foobar
                     *
                     * @return Foobar|Barbar
                     */
                    public function foo()
                    {
                    }
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo(): Foobar|Barbar',
                    'documentation' => '',
                    'snippet' => 'foo()',
                ]
            ]
        ];

        yield 'Virtual method' => [
            <<<'EOT'
                <?php

                /**
                 * @method \Foobar foo()
                 */
                interface Barfoo {}

                class Foobar implements Barfoo
                {
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo(): Foobar',
                    'snippet' => 'foo()',
                ]
            ]
        ];

        yield 'Virtual static method' => [
            <<<'EOT'
                <?php

                /**
                 * @method static \Foobar foo()
                 */
                interface Barfoo {}

                class Foobar implements Barfoo
                {
                }

                $foobar = new Foobar();
                $foobar::<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo(): Foobar',
                    'snippet' => 'foo()',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'Foobar',
                ],
            ]
        ];

        yield 'Static property' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public static $foo;
                }

                $foobar = new Foobar();
                $foobar::<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => '$foo',
                    'short_description' => 'pub static $foo',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'Foobar',
                ],
            ]
        ];

        yield 'Static property with previous arrow accessor' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public static $foo;

                    /**
                     * @var Foobar
                     */
                    public $me;
                }

                $foobar = new Foobar();
                $foobar->me::<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => '$foo',
                    'short_description' => 'pub static $foo',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'Foobar',
                ],
            ]
        ];

        yield 'Partially completed method with text after' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function aaa()
                    {
                        $this->bb<>new Foobar();
                    }

                    public function bbb() {}
                    public function ccc() {}
                }

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'bbb',
                    'short_description' => 'pub bbb()',
                    'snippet' => 'bbb()',
                ]
            ]
        ];

        yield 'Partially completed static method with brackets' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function aaa()
                    {
                        self::bb<>();
                    }

                    public static function bbb() {}
                    public static function ccc() {}
                }

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'bbb',
                    'short_description' => 'pub bbb()',
                    'snippet' => 'bbb',
                ]
            ]
        ];

        yield 'Partially completed static method with text after' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function aaa()
                    {
                        self::bb<>new Foobar();
                    }

                    public static function bbb() {}
                    public static function ccc() {}
                }

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'bbb',
                    'short_description' => 'pub bbb()',
                    'snippet' => 'bbb()',
                ]
            ]
        ];

        yield 'Partially completed 3' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public static $foobar;
                    public static $barfoo;
                }

                $foobar = new Foobar();
                $foobar::$f<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => '$foobar',
                    'short_description' => 'pub static $foobar',
                ]
            ]
        ];

        yield 'Partially completed 2' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function aaa()
                    {
                        $this->bb<>
                    }

                    public function bbb() {}
                    public function ccc() {}
                }

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'bbb',
                    'short_description' => 'pub bbb()',
                    'snippet' => 'bbb()',
                ]
            ]
        ];

        yield 'Partially completed' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    const FOOBAR = 'foobar';
                    const BARFOO = 'barfoo';
                }

                $foobar = new Foobar();
                $foobar::<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'BARFOO',
                    'short_description' => 'BARFOO = "barfoo"',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'FOOBAR',
                    'short_description' => 'FOOBAR = "foobar"',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'Foobar',
                ],
            ],
        ];

        yield 'Constant visibility from outside' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    const FOOBAR = 'foobar';
                    private const BARFOO = 'barfoo';
                    protected const BARFOX = 'barfox';
                }

                $foobar = new Foobar();
                $foobar::<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'FOOBAR',
                    'short_description' => 'FOOBAR = "foobar"',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'Foobar',
                ],
            ],
        ];

        yield 'Constant visibility from inside' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    const FOOBAR = 'foobar';
                    private const BARFOO = 'barfoo';
                    protected const BARFOX = 'barfox';

                    public function fog(): void
                    {
                        $foobar = self::<>
                    }
                }

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'BARFOO',
                    'short_description' => 'BARFOO = "barfoo"',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'BARFOX',
                    'short_description' => 'BARFOX = "barfox"',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'FOOBAR',
                    'short_description' => 'FOOBAR = "foobar"',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'Foobar',
                ],
            ],
        ];

        yield 'Accessor on new line' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public $foobar;
                }

                $foobar = new Foobar();
                $foobar
                    ->    <>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => 'foobar',
                    'short_description' => 'pub $foobar',
                ],
            ],
        ];

        yield 'Completion on collection' => [
            <<<'EOT'
                <?php

                class Collection
                {
                    public function heyho() {}
                }

                class Foobar
                {
                    /**
                     * @return Collection<Foobar>
                     */
                    public function collection() {}
                }

                $foobar = new Foobar();
                $collection = $foobar->collection();
                $collection-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'heyho',
                    'short_description' => 'pub heyho()',
                    'snippet' => 'heyho()',
                ],
            ],
        ];

        yield 'Completion on assignment' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function method1() {}
                }

                $foobar = new Foobar();
                $foobar = $foobar->meth<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'method1',
                    'short_description' => 'pub method1()',
                    'snippet' => 'method1()',
                ],
            ],
        ];

        yield 'member is variable name' => [
            <<<'EOT'
                <?php

                class BarBar
                {
                    public $barbar;
                }

                class Foobar
                {
                    /**
                     * @var BarBar
                     */
                    public $foobar;
                }

                $barbar = 'foobar';
                $foobar = new Foobar();
                $foobar->$bar<>;
                EOT
            , [
            ]
        ];

        yield 'chained method call with arguments' => [
            <<<'EOT'
                <?php

                class BarBar {
                    public function hello($one, $two): Foobar {}
                }

                class Foobar {
                    public function goodbye(): BarBar {}
                }

                $foobar = (new Foobar())
                    ->goodbye()
                    ->hello('one', 'two')
                    -><>
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'goodbye',
                    'snippet' => 'goodbye()',
                ],
            ]
        ];

        yield 'chained static method call with arguments' => [
            <<<'EOT'
                <?php

                class BarBar {
                    public static function hello($one, $two): Foobar {}
                }

                class Foobar {
                    public function goodbye(): BarBar {}
                }

                $foobar = Foobar::goodbye()
                    ->hello('one', 'two')
                    -><>
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'goodbye',
                    'snippet' => 'goodbye()',
                ],
            ]
        ];

        yield 'instance member on static method' => [
            <<<'EOT'
                <?php

                class BarBar {
                    public static function hello() {}
                    public function goodbye() {}
                }

                BarBar::<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'hello',
                    'snippet' => 'hello()',
                ],
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'class',
                    'short_description' => 'BarBar',
                ],
            ]
        ];

        yield 'shows static member on instance method' => [
            <<<'EOT'
                <?php

                class BarBar {
                    public function hello() {}
                    public static function goodbye() {}
                }

                $bar = new BarBar();
                $bar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'goodbye',
                    'snippet' => 'goodbye()',
                ],
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'hello',
                    'snippet' => 'hello()',
                ],
            ]
        ];

        yield 'static property' => [
            <<<'EOT'
                <?php

                class BarBar {
                    /** @var Foo */
                    public static $foo;
                }

                BarBar::$f<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => '$foo',
                    'short_description' => 'pub static $foo: Foo',
                ],
            ]
        ];

        yield 'union' => [
            <<<'EOT'
                <?php

                namespace Foo;

                class Foobar {
                    public function fun(): string {}
                }
                class Barfoo {
                    public function not(): string {}
                }

                function create(): Foobar|Barfoo {}

                $f = create();
                $f-><>;

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'fun',
                    'short_description' => 'pub fun(): string',
                    'snippet' => 'fun()',
                ],
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'not',
                    'short_description' => 'pub not(): string',
                    'snippet' => 'not()',
                ],
            ]
        ];

        yield 'enum' => [
            <<<'EOT'
                <?php

                enum Enum1 {
                    case FOOBAR;
                    const FOO = 'FOO';
                }

                Enum1::F<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'FOO',
                    'short_description' => 'FOO = "FOO"',
                ],
                [
                    'type' => Suggestion::TYPE_ENUM,
                    'name' => 'FOOBAR',
                    'short_description' => 'case FOOBAR',
                ],
            ]
        ];

        yield 'enum case' => [
            <<<'EOT'
                <?php

                enum Enum1: string {
                    case FOOBAR = 'bar';
                }

                Enum1::FOOBAR-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'cases',
                    'short_description' => 'pub cases(): BackedEnumCase[]',
                    'snippet' => 'cases()',
                ],
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'from',
                    'short_description' => 'pub from(int|string $value): static(Enum1)',
                    'snippet' => 'from(${1:\\$value})${0}',
                ],
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => 'name',
                ],
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'tryFrom',
                    'short_description' => 'pub tryFrom(int|string $value): static(Enum1)|null',
                    'snippet' => 'tryFrom(${1:\\$value})${0}',
                ],
                [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'name' => 'value',
                ],
            ]
        ];

        /** See https://github.com/phpactor/phpactor/issues/1612
        yield 'backed enum' => [
            <<<'EOT'
                <?php

                enum Enum1 {
                    case FOOBAR = 'bar';
                }

                Enum1::F<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_ENUM,
                    'name' => 'FOOBAR',
                    'short_description' => 'case FOOBAR = "bar"',
                ],
            ]
        ];
         */

        yield 'nullable' => [
            <<<'EOT'
                <?php

                namespace Foo;

                class Barfoo {
                    public function not(): string {}
                }

                function create(): ?Barfoo {}

                $f = create();
                $f-><>;

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'not',
                    'short_description' => 'pub not(): string',
                    'snippet' => 'not()',
                ],
            ]
        ];

        yield 'No constants for instance' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    const FOOBAR = 'foobar';
                    const BARFOO = 'barfoo';

                    public function bar(): string {}
                }

                $foobar = new Foobar();
                $foobar-><>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'bar',
                    'short_description' => 'pub bar(): string',
                    'snippet' => 'bar()'
                ],
            ],
        ];

        yield 'parent::' => [
            <<<'EOT'
                <?php

                abstract class Bar {
                    public function baz(): string {}
                }

                class Foobar extends Bar
                {
                    const FOOBAR = 'foobar';
                    const BARFOO = 'barfoo';

                    public function bar(): string {
                        parent::<>
                    }
                }
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'baz',
                    'short_description' => 'pub baz(): string',
                    'snippet' => 'baz()'
                ],
            ],
        ];

        yield 'parent::__construct' => [
            <<<'EOT'
                <?php

                class Bar {
                    public function __construct(string $foo) {}
                }

                class Foobar extends Bar
                {
                    public function bar(): string {
                        parent::<>
                    }
                }
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => '__construct',
                    'short_description' => 'pub __construct(string $foo)',
                    'snippet' => '__construct(${1:\\$foo})${0}'
                ],
            ],
        ];

        yield 'parenthesized type' => [
            <<<'EOT'
                <?php

                interface Zero {}
                interface OneIn extends Zero {}
                interface TwoIn extends Zero {}
                class One implements OneIn { public function foo(); }
                class Two implements TwoIn { public function foo(); }

                function (Zero $zero) {
                    if (!$zero instanceof One && !$zero instanceof Two) {
                        return;
                    }

                    $zero-><>
                }
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo()',
                    'snippet' => 'foo()'
                ],
                [
                    'type' => Suggestion::TYPE_METHOD,
                    'name' => 'foo',
                    'short_description' => 'pub foo()',
                    'snippet' => 'foo()'
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideCouldNotComplete
     */
    public function testCouldNotComplete(string $source): void
    {
        $this->assertCouldNotComplete($source);
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideCouldNotComplete(): Generator
    {
        yield 'non member access' => [ '<?php $hello<>' ];
        yield 'variable with previous accessor' => [ '<?php $foobar->hello; $hello<>' ];
        yield 'statement with previous member access' => [ '<?php if ($foobar && $this->foobar) { echo<>' ];
        yield 'variable with previous static member access' => [ '<?php Hello::hello(); $foo<>' ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()
            ->addMemberProvider(new DocblockMemberProvider())
            ->withCacheForDocument(new CacheForDocument(fn () => new StaticCache()))
            ->addSource($source)->build();

        return new WorseClassMemberCompletor(
            $reflector,
            $this->formatter(),
            $this->snippetFormatter($reflector),
            ObjectRendererBuilder::create()->renderEmptyOnNotFound()->build()
        );
    }
}
