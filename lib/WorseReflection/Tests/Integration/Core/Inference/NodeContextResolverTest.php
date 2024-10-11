<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\PHPStanDocblockParserFactory;
use Phpactor\WorseReflection\Core\Cache\StaticCache;
use Phpactor\WorseReflection\Core\DefaultResolverFactory;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\PropertyAssignments;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\NodeContextFromMemberAccess;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TestUtils\ExtractOffset;
use RuntimeException;

class NodeContextResolverTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        //var_dump($this->logger());
    }

    /**
     * @dataProvider provideGeneral
     */
    public function testGeneral(string $source, array $locals, array $expectedInformation): void
    {
        $variables = [];
        $properties = [];
        $offset = 0;
        foreach ($locals as $name => $varSymbolInfo) {
            $offset++;
            if ($varSymbolInfo instanceof Type) {
                $varSymbolInfo = NodeContext::for(
                    Symbol::fromTypeNameAndPosition(
                        'variable',
                        $name,
                        ByteOffsetRange::fromInts($offset, $offset)
                    )
                )->withType($varSymbolInfo);
            }

            $variable = Variable::fromSymbolContext($varSymbolInfo);

            if (Symbol::PROPERTY === $varSymbolInfo->symbol()->symbolType()) {
                $properties[$varSymbolInfo->symbol()->position()->start()->toInt()] = $variable;

                continue;
            }

            $variables[$varSymbolInfo->symbol()->position()->start()->toInt()] = $variable;
        }

        $symbolInfo = $this->resolveNodeAtOffset(
            LocalAssignments::fromArray($variables),
            PropertyAssignments::fromArray($properties),
            $source,
        );
        $this->assertExpectedInformation($expectedInformation, $symbolInfo);
    }

    /**
     * @dataProvider provideValues
     */
    public function testValues(string $source, array $variables, array $expected): void
    {
        $information = $this->resolveNodeAtOffset(
            LocalAssignments::fromArray($variables),
            PropertyAssignments::create(),
            $source,
        );
        $this->assertExpectedInformation($expected, $information);
    }

    /**
     * These tests test the case where a class in the resolution tree was not found, however
     * their usefulness is limited because we use the StringSourceLocator for these tests which
     * "always" finds the source.
     *
     * @dataProvider provideNotResolvableClass
     */
    public function testNotResolvableClass(string $source): void
    {
        $value = $this->resolveNodeAtOffset(
            LocalAssignments::fromArray([
                0 => Variable::fromSymbolContext(
                    NodeContext::for(Symbol::fromTypeNameAndPosition(
                        Symbol::CLASS_,
                        'bar',
                        ByteOffsetRange::fromInts(0, 0)
                    ))->withType(TypeFactory::fromString('Foobar'))
                )
            ]),
            PropertyAssignments::create(),
            $source
        );
        $this->assertEquals(TypeFactory::unknown(), $value->type());
    }

    public function provideGeneral()
    {
        yield 'It should return none value for whitespace' => [
            '  <>  ', [],
            ['type' => '<missing>'],
        ];

        yield 'It should return the name of a class' => [
            <<<'EOT'
                                    <?php

                                    $foo = new Cl<>assName();

                EOT
        , [], ['type' => 'ClassName', 'symbol_type' => Symbol::CLASS_]
        ];

        yield 'It should return the fully qualified name of a class' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                $foo = new Cl<>assName();

                EOT
                        , [], ['type' => 'Foobar\Barfoo\ClassName']
        ];

        yield 'It should return the fully qualified name of a with an imported name.' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use BarBar\ClassName();

                $foo = new Clas<>sName();

                EOT
            , [], ['type' => 'BarBar\ClassName', 'symbol_type' => Symbol::CLASS_, 'symbol_name' => 'ClassName']
        ];

        yield 'It should return the fully qualified name of a use definition' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use BarBar\Clas<>sName();

                $foo = new ClassName();

                EOT
            , [], ['type' => 'BarBar\ClassName']
        ];

        yield 'It returns the FQN of a method parameter with a default' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                class Foobar
                                {
                                    public function foobar(Barfoo $<>barfoo = 'test')
                                    {
                                    }
                                }

                EOT
        , [], ['type' => 'Foobar\Barfoo\Barfoo', 'symbol_type' => Symbol::VARIABLE, 'symbol_name' => 'barfoo']
        ];

        yield 'It returns the type and value of a scalar method parameter' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                class Foobar
                                {
                                    public function foobar(string $b<>arfoo = 'test')
                                    {
                                    }
                                }

                EOT
            , [], ['type' => 'string']
        ];

        yield 'It returns the value of a method parameter with a constant' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                class Foobar
                                {
                                    public function foobar(string $ba<>rfoo = 'test')
                                    {
                                    }
                                }

                EOT
            , [], ['type' => 'string']
        ];

        yield 'It returns the FQN of a method parameter in an interface' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                use Acme\Factory;

                                interface Foobar
                                {
                                    public function hello(World $wor<>ld);
                                }

                EOT
            , [], ['type' => 'Foobar\Barfoo\World']
        ];

        yield 'It returns the FQN of a method parameter in a trait' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                use Acme\Factory;

                                trait Foobar
                                {
                                    public function hello(<>World $world)
                                    {
                                    }
                                }

                EOT
            , [], ['type' => 'Foobar\Barfoo\World', 'symbol_type' => Symbol::CLASS_, 'symbol_name' => 'World']
        ];

        yield 'It returns the value of a method parameter' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                class Foobar
                                {
                                    public function foobar(string $<>barfoo = 'test')
                                    {
                                    }
                                }

                EOT
            , [], ['type' => 'string']
        ];

        yield 'Ignores parameter on anonymous class' => [
            <<<'EOT'
                            <?php

                            class Foobar {

                                public function foobar()
                                {
                                    $class = new class { public function __invoke($foo<>bar) {} };
                                }
                            }

                EOT
            , [], ['type' => '<missing>', 'symbol_type' => '<unknown>', 'symbol_name' => 'Parameter']
        ];

        yield 'It returns the FQN of a static call' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use Acme\Factory;

                $foo = Fac<>tory::create();

                EOT
                            , [], ['type' => 'Acme\Factory', 'symbol_type' => Symbol::CLASS_]
        ];

        yield 'It returns the FQN of a method parameter' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                use Acme\Factory;

                                class Foobar
                                {
                                    public function hello(W<>orld $world)
                                    {
                                    }
                                }

                EOT
        , [], ['type' => 'Foobar\Barfoo\World']
        ];

        yield 'It resolves a anonymous function use' => [
            <<<'EOT'
                                <?php

                                function ($blah) use ($f<>oo) {

                                }

                EOT
            , [ 'foo' => TypeFactory::fromString('string') ], ['type' => 'string', 'symbol_type' => Symbol::VARIABLE, 'symbol_name' => 'foo']
        ];

        yield 'It resolves an undeclared variable' => [
            <<<'EOT'
                                    <?php

                                    $b<>lah;

                EOT
        , [], ['type' => '<missing>', 'symbol_type' => Symbol::VARIABLE, 'symbol_name' => 'blah']
        ];

        yield 'It returns the FQN of variable assigned in frame' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                use Acme\Factory;

                                class Foobar
                                {
                                    public function hello(World $world)
                                    {
                                        echo $w<>orld;
                                    }
                                }

                EOT
            , [ 'world' => TypeFactory::fromString('World') ], ['type' => 'World', 'symbol_type' => Symbol::VARIABLE, 'symbol_name' => 'world']
        ];

        yield 'It returns type for a call access expression' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                class Type3
                                {
                                    public function foobar(): Foobar
                                    {
                                    }
                                    }

                                class Type2
                                {
                                    public function type3(): Type3
                                    {
                                    }
                                }

                                class Type1
                                {
                                    public function type2(): Type2
                                    {
                                    }
                                }

                                class Foobar
                                {
                                    /**
                                     * @var Type1
                                     */
                                    private $foobar;

                                    public function hello(Barfoo $world)
                                    {
                                        $this->foobar->type2()->type3(<>);
                                    }
                                }
                EOT
            , [
                'this' => TypeFactory::fromString('Foobar\Barfoo\Foobar'),
            ], [
                'type' => 'Foobar\Barfoo\Type3',
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'type3',
                'container_type' => 'Foobar\Barfoo\Type2',
            ],
        ];

        yield 'It returns type for a method which returns an interface type' => [
            <<<'EOT'
                                <?php

                                interface Barfoo
                                {
                                    public function foo(): string;
                                }

                                class Foobar
                                {
                                    public function hello(): Barfoo
                                    {
                                    }

                                    public function goodbye()
                                    {
                                        $this->hello()->foo(<>);
                                    }
                                }
                EOT
            , [
                'this' => TypeFactory::fromString('Foobar'),
            ], [
                'type' => 'string',
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'foo',
                'container_type' => 'Barfoo',
            ],
        ];

        yield 'It returns class type for parent class for parent method' => [
            <<<'EOT'
                                <?php

                                class Type3 {}

                                class Barfoo
                                {
                                    public function type3(): Type3
                                    {
                                    }
                                }

                                class Foobar extends Barfoo
                                {
                                    /**
                                     * @var Type1
                                     */
                                    private $foobar;

                                    public function hello(Barfoo $world)
                                    {
                                        $this->type3(<>);
                                    }
                                }
                EOT
            , [
                'this' => TypeFactory::fromString('Foobar'),
            ], [
                'type' => 'Type3',
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'type3',
                'container_type' => 'Foobar',
            ],
        ];

        yield 'It returns type for a property access when class has method of same name' => [
            <<<'EOT'
                                <?php

                                class Type1
                                {
                                    public function asString(): string
                                    {
                                    }
                                }

                                class Foobar
                                {
                                    /**
                                     * @var Type1
                                     */
                                    private $foobar;

                                    private function foobar(): Hello
                                    {
                                    }

                                    public function hello()
                                    {
                                        $this->foobar->asString(<>);
                                    }
                                }
                EOT
            , [
                'this' => TypeFactory::fromString('Foobar'),
            ], ['type' => 'string'],
        ];

        yield 'It returns type for a new instantiation' => [
            <<<'EOT'
                                    <?php

                                    new <>Bar();
                EOT
        , [], ['type' => 'Bar'],
        ];

        yield 'It returns type for a new instantiation from a variable' => [
            <<<'EOT'
                                    <?php

                                    new $<>foobar;
                EOT
        , [
            'foobar' => TypeFactory::fromString('Foobar'),
        ], ['type' => 'Foobar'],
        ];

        yield 'It returns type for string literal' => [
            <<<'EOT'
                                    <?php

                                    'bar<>';
                EOT
        , [], ['type' => '"bar"', 'symbol_type' => Symbol::STRING ]
        ];

        yield 'It returns type for float' => [
            <<<'EOT'
                                    <?php

                                    1.<>2;
                EOT
        , [], ['type' => '1.2', 'symbol_type' => Symbol::NUMBER],
        ];

        yield 'It returns type for integer' => [
            <<<'EOT'
                                    <?php

                                    12<>;
                EOT
        , [], ['type' => '12', 'symbol_type' => Symbol::NUMBER],
        ];

        yield 'It returns type for octal integer' => [
            <<<'EOT'
                                    <?php

                                    012<>;
                EOT
        , [], ['type' => '012', 'symbol_type' => Symbol::NUMBER],
        ];

        yield 'It returns type for hexadecimal integer' => [
            <<<'EOT'
                                    <?php

                                    0x1A<>;
                EOT
        , [], ['type' => '0x1A', 'symbol_type' => Symbol::NUMBER],
        ];

        yield 'It returns type for binary integer' => [
            <<<'EOT'
                                    <?php

                                    0b11<>;
                EOT
        , [], ['type' => '0b11', 'symbol_type' => Symbol::NUMBER],
        ];

        yield 'It returns type for bool true' => [
            <<<'EOT'
                                    <?php

                                    tr<>ue;
                EOT
        , [], ['type' => 'true', 'symbol_type' => Symbol::BOOLEAN],
        ];

        yield 'It returns type for bool false' => [
            <<<'EOT'
                                    <?php

                                    <>false;
                EOT
        , [], ['type' => 'false', 'symbol_type' => Symbol::BOOLEAN],
        ];

        yield 'It returns type null' => [
            <<<'EOT'
                                    <?php

                                    n<>ull;
                EOT
        , [], ['type' => 'null',   ]             ];

        yield 'It returns type null case insensitive' => [
            <<<'EOT'
                                    <?php

                                    N<>ULL;
                EOT
        , [], ['type' => 'null',   ]             ];

        yield 'It returns type and value for an array' => [
            <<<'EOT'
                                    <?php

                                    [ 'one' => 'two', 'three' => 3 <>];
                EOT
        , [], ['type' => 'array{one:"two",three:3}'],
        ];

        yield 'Empty array' => [
            <<<'EOT'
                                    <?php

                                    [  <>];
                EOT
        , [], ['type' => 'array{}']];

        yield 'It type for a class constant' => [
            <<<'EOT'
                                <?php

                                $foo = Foobar::HELL<>O;

                                class Foobar
                                {
                                    const HELLO = 'string';
                                }
                EOT
            , [], ['type' => '"string"'],
        ];

        yield 'Static method access' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    public static function foobar(): Hello {}
                                }

                                Foobar::fooba<>r();

                                class Hello
                                {
                                }
                EOT
            , [], ['type' => 'Hello'],
        ];

        yield 'Static constant access' => [
            <<<'EOT'
                                <?php

                                Foobar::HELLO_<>CONSTANT;

                                class Foobar
                                {
                                    const HELLO_CONSTANT = 'hello';
                                }
                EOT
            , [], ['type' => '"hello"'],
        ];

        yield 'Static property access' => [
            <<<'EOT'
                                    <?php

                                    Foobar::$my<>Property;

                                    class Foobar
                                    {
                                        /** @var string */
                                        public static $myProperty = 'hello';
                                    }
                EOT
            , [], [
                'type' => 'string',
                'symbol_type' => Symbol::PROPERTY,
                'symbol_name' => 'myProperty',
                'container_type' => 'Foobar',
            ],
        ];

        yield 'Static property access 2' => [
            <<<'EOT'
                                    <?php

                                    class Foobar
                                    {
                                        /** @var string */
                                        public static $myProperty = 'hello';

                                        function m() {
                                            self::$my<>Property = 5;
                                        }
                                    }
                EOT
            , [], [
                'type' => 'string',
                'symbol_type' => Symbol::PROPERTY,
                'symbol_name' => 'myProperty',
                'container_type' => 'Foobar',
            ],
        ];

        yield 'Static property access instance)' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                /** @var string */
                public static $myProperty = 'hello';
                }

                $foobar = new Foobar();
                $foobar::$my<>Property = 5;
                EOT
                        , [
                            'foobar' => TypeFactory::fromString('Foobar')
                        ], [
                            'type' => 'string',
                            'symbol_type' => Symbol::PROPERTY,
                            'symbol_name' => 'myProperty',
                            'container_type' => 'Foobar',
                        ],
        ];

        yield 'Member access with variable' => [
            <<<'EOT'
                                <?php

                                $foobar = new Foobar();
                                $foobar->$barfoo(<>);

                                class Foobar
                                {
                                }
                EOT
        , [], ['type' => '<missing>'],
        ];

        yield 'Member access with valued variable' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello(): string {}
                }

                $foobar->$barfoo(<>);
                EOT
                            , [
                                'foobar' => TypeFactory::fromString('Foobar'),
                                'barfoo' => NodeContext::for(
                                    Symbol::fromTypeNameAndPosition(Symbol::STRING, 'barfoo', ByteOffsetRange::fromInts(0, 0))
                                )->withType(TypeFactory::stringLiteral('hello'))
                            ], ['type' => 'string'],
        ];

        yield 'It returns type of property' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    /**
                                     * @var stdClass
                                     */
                                    private $std<>Class;
                                }
                EOT
        , [], ['type' => 'stdClass', 'symbol_name' => 'stdClass'],
        ];

        yield 'It returns type for parenthesised new object' => [
            <<<'EOT'
                                    <?php

                                    (new stdClass())<>;
                EOT
        , [], ['type' => 'stdClass', 'symbol_name' => 'stdClass'],
        ];

        yield 'It resolves a clone expression' => [
            <<<'EOT'
                                    <?php

                                    (clone new stdClass())<>;
                EOT
        , [], ['type' => 'stdClass', 'symbol_name' => 'stdClass'],
        ];

        yield 'It returns the FQN of variable assigned in frame 2' => [
            <<<'EOT'
                                <?php

                                namespace Foobar\Barfoo;

                                use Acme\Factory;
                                use Acme\FactoryInterface;

                                class Foobar
                                {
                                    /**
                                     * @var FactoryInterface
                                     */
                                    private $bar;

                                    public function hello(World $world)
                                    {
                                        assert($this->bar instanceof Factory);

                                        $this->ba<>r
                                    }
                                }

                EOT
            , [
                'this' => TypeFactory::class('Foobar\Barfoo\Foobar'),
                'bar' => NodeContext::for(Symbol::fromTypeNameAndPosition(
                    Symbol::PROPERTY,
                    'bar',
                    ByteOffsetRange::fromInts(0, 0),
                ))
                    ->withContainerType(TypeFactory::class('Foobar\Barfoo\Foobar'))
                    ->withType(TypeFactory::class('Acme\Factory')),
            ], [
                'types' => [
                    TypeFactory::class('Acme\Factory'),
                ],
                'symbol_type' => Symbol::PROPERTY,
                'symbol_name' => 'bar',
            ]
        ];
    }

    public function provideValues()
    {
        yield 'It returns type for self' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    public function foobar(Barfoo $barfoo = 'test')
                                    {
                                        sel<>f::
                                    }
                                }
                EOT
        , [], ['type' => 'Foobar']
        ];

        yield 'It returns type for static' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    public function foobar(Barfoo $barfoo = 'test')
                                    {
                                        stat<>ic::
                                    }
                                }
                EOT
            , [], ['type' => 'Foobar']
        ];

        yield 'It returns type for parent' => [
            <<<'EOT'
                                <?php

                                class ParentClass {}

                                class Foobar extends ParentClass
                                {
                                    public function foobar(Barfoo $barfoo = 'test')
                                    {
                                        pare<>nt::
                                    }
                                }
                EOT
            , [], ['type' => 'ParentClass']
        ];

        yield 'It assumes true for ternary expressions' => [
            <<<'EOT'
                                    <?php

                                    $barfoo ? <>'foobar' : 'barfoo';
                EOT
        , [], ['type' => '"foobar"', ]
        ];

        yield 'It uses condition value if ternery "if" is empty' => [
            <<<'EOT'
                                    <?php

                                    'string' ?:<> new \stdClass();
                EOT
        , [], ['type' => '"string"', ]
        ];

        yield 'It shows the symbol name for a method declartion' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    public function me<>thod()
                                    {
                                    }
                                }
                EOT
            , [], [
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'method',
                'container_type' => 'Foobar',
            ]
        ];

        yield 'Class name' => [
            <<<'EOT'
                                <?php

                                class Fo<>obar
                                {
                                }
                EOT
            , [], ['type' => 'Foobar', 'symbol_type' => Symbol::CLASS_, 'symbol_name' => 'Foobar'],
        ];

        yield 'Property name' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    private $a<>aa = 'asd';
                                }
                EOT
            , [], ['type' => '<missing>', 'symbol_type' => Symbol::PROPERTY, 'symbol_name' => 'aaa', 'container_type' => 'Foobar'],
        ];

        yield 'Constant name' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    const AA<>A = 'aaa';
                                }
                EOT
            , [], [
                'type' => '<missing>',
                'symbol_type' => Symbol::CONSTANT,
                'symbol_name' => 'AAA',
                'container_type' => 'Foobar'
            ],
        ];

        yield 'Enum case name' => [
            <<<'EOT'
                                    <?php

                                    enum Foobar
                                    {
                                        case AA<>A = 'aaa';
                                    }
                EOT
            , [], [
                'type' => '<missing>',
                'symbol_type' => Symbol::CASE,
                'symbol_name' => 'AAA',
                'container_type' => 'Foobar'
            ],
        ];

        yield 'Enum const' => [
            <<<'EOT'
                                    <?php

                                    enum Foobar
                                    {
                                        public const AA<>A = 'aaa';
                                    }
                EOT
            , [], [
                'type' => '<missing>',
                'symbol_type' => Symbol::CONSTANT,
                'symbol_name' => 'AAA',
                'container_type' => 'Foobar'
            ],
        ];

        yield 'Function name' => [
            <<<'EOT'
                                <?php

                                function f<>oobar()
                                {
                                }
                EOT
            , [], ['symbol_type' => Symbol::FUNCTION, 'symbol_name' => 'foobar'],
        ];


        yield 'Function call' => [
            <<<'EOT'
                <?php

                function hello(): string;

                hel<>lo();
                EOT
                            , [], ['type' => 'string', 'symbol_type' => Symbol::FUNCTION, 'symbol_name' => 'hello'],
        ];

        yield 'Trait name' => [
            <<<'EOT'
                                <?php

                                trait Bar<>bar
                                {
                                }
                EOT
        , [], ['symbol_type' => 'class', 'symbol_name' => 'Barbar', 'type' => 'Barbar' ],
        ];
    }

    public function provideNotResolvableClass()
    {
        yield 'Calling property method for non-existing class' => [
            <<<'EOT'
                                <?php

                                class Foobar
                                {
                                    /**
                                     * @var NonExisting
                                     */
                                    private $hello;

                                    public function hello()
                                    {
                                        $this->hello->foobar(<>);
                                    }
                                }
                EOT
        ];

        yield 'Class extends non-existing class' => [
            <<<'EOT'
                            <?php

                            class Foobar extends NonExisting
                            {
                                public function hello()
                                {
                                    $hello = $this->foobar(<>);
                                }
                            }
                EOT
        ];

        yield 'Method returns non-existing class' => [
            <<<'EOT'
                            <?php

                            class Foobar
                            {
                                private function hai(): Hai
                                {
                                }

                                public function hello()
                                {
                                    $this->hai()->foo(<>);
                                }
                            }
                EOT
        ];

        yield 'Method returns class which extends non-existing class' => [
            <<<'EOT'
                            <?php

                            class Foobar
                            {
                                private function hai(): Hai
                                {
                                }

                                public function hello()
                                {
                                    $this->hai()->foo(<>);
                                }
                            }

                            class Hai extends NonExisting
                            {
                            }
                EOT
        ];


        yield 'Static method returns non-existing class' => [
            <<<'EOT'
                            <?php

                            ArrGoo::hai()->foo(<>);

                            class Foobar
                            {
                                public static function hai(): Foo
                                {
                                }
                            }
                EOT
        ];
    }

    public function testAttachesScope(): void
    {
        $source = <<<'EOT'
            <?php

            namespace Hello;

            use Goodbye;
            use Adios;

            new Foob<>o;
            EOT
        ;
        $context = $this->resolveNodeAtOffset(
            LocalAssignments::create(),
            PropertyAssignments::create(),
            $source,
        );
        $this->assertCount(2, $context->scope()->nameImports());
    }

    private function resolveNodeAtOffset(
        LocalAssignments $locals,
        PropertyAssignments $properties,
        string $source
    ): NodeContext {
        $frame = new Frame($locals, $properties);

        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = $this->parseSource($source)->getDescendantNodeAtPosition($offset);

        $reflector = $this->createReflector($source);
        $nameResolver = new NodeToTypeConverter($reflector, $this->logger());
        $resolver = new NodeContextResolver(
            $reflector,
            new PHPStanDocblockParserFactory($reflector),
            $this->logger(),
            new StaticCache(),
            (new DefaultResolverFactory(
                $reflector,
                $nameResolver,
                new GenericMapResolver($reflector),
                new NodeContextFromMemberAccess(
                    new GenericMapResolver($reflector),
                    []
                )
            ))->createResolvers(),
        );

        return $resolver->resolveNode($frame, $node);
    }

    private function assertExpectedInformation(array $expectedInformation, NodeContext $information): void
    {
        foreach ($expectedInformation as $name => $value) {
            switch ($name) {
                case 'type':
                    $this->assertEquals($value, (string) $information->type(), $name);
                    continue 2;
                case 'types':
                    $this->assertEquals(
                        Type::fromTypes(...$value)->__toString(),
                        $information->type()->__toString(),
                        $name,
                    );
                    continue 2;
                case 'symbol_type':
                    $this->assertEquals($value, $information->symbol()->symbolType(), $name);
                    continue 2;
                case 'symbol_name':
                    $this->assertEquals($value, $information->symbol()->name(), $name);
                    continue 2;
                case 'container_type':
                    $this->assertEquals($value, (string) $information->containerType(), $name);
                    continue 2;
                case 'log':
                    $this->assertStringContainsString($value, implode(' ', $this->logger->messages()), $name);
                    continue 2;
                default:
                    throw new RuntimeException(sprintf('Do not know how to test symbol information attribute "%s"', $name));
            }
        }
    }
}
