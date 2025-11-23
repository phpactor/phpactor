<?php

namespace Phpactor\CodeBuilder\Tests\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;
use Phpactor\CodeBuilder\Domain\Prototype\Constant;
use Phpactor\CodeBuilder\Domain\Prototype\Constants;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\Docblock;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Prototype\InterfacePrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Interfaces;
use Phpactor\CodeBuilder\Domain\Prototype\Line;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\MethodBody;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Property;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\TraitPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Traits;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatements;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;
use Phpactor\CodeBuilder\Domain\Prototype\Value;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Renderer;

abstract class GeneratorTestCase extends TestCase
{
    #[DataProvider('provideRender')]
    #[TestDox('It should use twig to render a template')]
    public function testRender(Prototype $prototype, string $expectedCode): void
    {
        $code = $this->renderer()->render($prototype);
        $this->assertEquals(rtrim(Code::fromString($expectedCode), "\n"), rtrim($code, "\n"));
    }

    /**
     * @return Generator<string, array{Prototype, string}>
     */
    public static function provideRender(): Generator
    {
        yield 'Renders an empty PHP file' => [
            new SourceCode(),
            '<?php',
        ];

        yield 'Renders a PHP file with a namespace' => [
            new SourceCode(
                NamespaceName::fromString('Acme')
            ),
            <<<'EOT'
                <?php

                namespace Acme;
                EOT
        ];

        yield 'Renders source code with classes' => [
                new SourceCode(
                    NamespaceName::root(),
                    UseStatements::empty(),
                    Classes::fromClasses([ new ClassPrototype('Dog'), new ClassPrototype('Cat') ])
                ),
                <<<'EOT'
                    <?php

                    class Dog
                    {
                    }

                    class Cat
                    {
                    }
                    EOT
            ];

        yield 'Renders source code with interfaces' => [
                new SourceCode(
                    NamespaceName::root(),
                    UseStatements::empty(),
                    Classes::empty(),
                    Interfaces::fromInterfaces([ new InterfacePrototype('Cat'), new InterfacePrototype('Squirrel') ])
                ),
                <<<'EOT'
                    <?php

                    interface Cat
                    {
                    }

                    interface Squirrel
                    {
                    }
                    EOT
        ];

        yield 'Renders source code with traits' => [
                new SourceCode(
                    NamespaceName::root(),
                    UseStatements::empty(),
                    Classes::empty(),
                    Interfaces::empty(),
                    Traits::fromTraits([ new TraitPrototype('Fox'), new TraitPrototype('Hare') ])
                ),
                <<<'EOT'
                    <?php

                    trait Fox
                    {
                    }

                    trait Hare
                    {
                    }
                    EOT
        ];
        yield 'Renders source code with use statements' => [
                        new SourceCode(
                            NamespaceName::root(),
                            UseStatements::fromUseStatements([
                                UseStatement::fromType('Acme\Post\Board'),
                                UseStatement::fromType('Acme\Post\Zebra')
                            ])
                        ),
                        <<<'EOT'
                            <?php

                            use Acme\Post\Board;
                            use Acme\Post\Zebra;
                            EOT
                    ];
        yield 'Renders a class' => [
                new ClassPrototype('Dog'),
                <<<'EOT'
                    class Dog
                    {
                    }
                    EOT
            ];
        yield 'Renders a class with properties' => [
                new ClassPrototype(
                    'Dog',
                    Properties::fromProperties([
                        new Property('planes')
                    ])
                ),
                <<<'EOT'
                    class Dog
                    {
                        public $planes;
                    }
                    EOT
            ];
        yield 'Renders a property' => [
                new Property('planes'),
                <<<'EOT'
                    public $planes;
                    EOT
            ];
        yield 'Renders private properties with default value' => [
                new Property('trains', Visibility::private(), DefaultValue::null()),
                <<<'EOT'
                    private $trains = null;
                    EOT
            ];
        yield 'Renders a class with constants' => [
                new ClassPrototype(
                    'Dog',
                    Properties::empty(),
                    Constants::fromConstants([
                        new Constant('AAA', Value::fromValue('aaa'))
                    ])
                ),
                <<<'EOT'
                    class Dog
                    {
                        const AAA = 'aaa';
                    }
                    EOT
            ];
        yield 'Renders a class with methods' => [
                new ClassPrototype(
                    'Dog',
                    Properties::empty(),
                    Constants::empty(),
                    Methods::fromMethods([
                        new Method('hello'),
                    ])
                ),
                <<<'EOT'
                    class Dog
                    {
                        public function hello()
                        {
                        }
                    }
                    EOT
            ];
        yield 'Renders a class method with a body' => [
                new ClassPrototype(
                    'Dog',
                    Properties::empty(),
                    Constants::empty(),
                    Methods::fromMethods([
                        new Method(
                            'hello',
                            null,
                            Parameters::empty(),
                            ReturnType::none(),
                            Docblock::none(),
                            0,
                            MethodBody::fromLines([
                                Line::fromString('$this->foobar = $barfoo;')
                            ])
                        ),
                    ])
                ),
                <<<'EOT'
                    class Dog
                    {
                        public function hello()
                        {
                            $this->foobar = $barfoo;
                        }
                    }
                    EOT
            ];
        yield 'Renders a method parameters' => [
                new Method('hello', Visibility::private(), Parameters::fromParameters([
                    new Parameter('one'),
                    new Parameter('two', Type::fromString('string')),
                    new Parameter('three', Type::none(), DefaultValue::fromValue(42)),
                ])),
                <<<'EOT'
                    private function hello($one, string $two, $three = 42)
                    EOT
            ];
        yield 'Renders a method nullable parameter' => [
                new Method('hello', Visibility::private(), Parameters::fromParameters([
                    new Parameter('two', Type::fromString('?string')),
                ])),
                <<<'EOT'
                    private function hello(?string $two)
                    EOT
            ];
        yield 'Renders a method parameter passed as a reference' => [
                new Method('hello', Visibility::private(), Parameters::fromParameters([
                    new Parameter('three', Type::none(), DefaultValue::none(), true),
                ])),
                <<<'EOT'
                    private function hello(&$three)
                    EOT
            ];
        yield 'Renders static method' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::none(),
                    Method::IS_STATIC
                ),
                <<<'EOT'
                    private static function hello()
                    EOT
            ];
        yield 'Renders abstract method' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::none(),
                    Method::IS_ABSTRACT
                ),
                <<<'EOT'
                    abstract private function hello()
                    EOT
            ];
        yield 'Renders method with a docblock' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::fromString('Hello bob')
                ),
                <<<'EOT'
                    /**
                     * Hello bob
                     */
                    private function hello()
                    EOT
            ];
        yield 'Renders method with a with special chars' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::fromString('<hello bob>')
                ),
                <<<'EOT'
                    /**
                     * <hello bob>
                     */
                    private function hello()
                    EOT
            ];
        yield 'Renders method return type' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::fromString('Hello')
                ),
                <<<'EOT'
                    private function hello(): Hello
                    EOT
            ];
        yield 'Renders method nullable return type' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::fromString('?Hello')
                ),
                <<<'EOT'
                    private function hello(): ?Hello
                    EOT
            ];
        yield 'Renders a class with a parent' => [
                new ClassPrototype(
                    'Kitten',
                    Properties::empty(),
                    Constants::empty(),
                    Methods::empty(),
                    ExtendsClass::fromString('Cat')
                ),
                <<<'EOT'
                    class Kitten extends Cat
                    {
                    }
                    EOT
            ];
        yield 'Renders a class with interfaces' => [
                new ClassPrototype(
                    'Kitten',
                    Properties::empty(),
                    Constants::empty(),
                    Methods::empty(),
                    ExtendsClass::none(),
                    ImplementsInterfaces::fromTypes([
                        Type::fromString('Feline'),
                        Type::fromString('Infant')
                    ])
                ),
                <<<'EOT'
                    class Kitten implements Feline, Infant
                    {
                    }
                    EOT
            ];
        yield 'Renders a property with a comment' => [
                new Property(
                    'planes',
                    Visibility::public(),
                    DefaultValue::none(),
                    Type::fromString('PlaneCollection')
                ),
                <<<'EOT'
                    /**
                     * @var PlaneCollection
                     */
                    public $planes;
                    EOT
            ];
        yield 'Renders an interface' => [
                new InterfacePrototype('Dog'),
                <<<'EOT'
                    interface Dog
                    {
                    }
                    EOT
            ];
        yield 'Renders an interface with methods' => [
                new InterfacePrototype('Dog', Methods::fromMethods([
                    new Method('hello'),
                ])),
                <<<'EOT'
                    interface Dog
                    {
                        public function hello();
                    }
                    EOT
            ];
        yield 'Renders a trait' => [
            new TraitPrototype(
                'Butterfly'
            ),
            <<<'EOT'
                trait Butterfly
                {
                }
                EOT
        ];

        yield 'Renders a trait with properties' => [
            new TraitPrototype(
                'Butterfly',
                Properties::fromProperties([ new Property('colour') ])
            ),
            <<<'EOT'
                trait Butterfly
                {
                    public $colour;
                }
                EOT
        ];

        yield 'Renders a trait with constants' => [
            new TraitPrototype(
                'Butterfly',
                Properties::empty(),
                Constants::fromConstants([
                    new Constant('WAS_CATERPILLAR', Value::fromValue(true)),
                ])
            ),
            <<<'EOT'
                trait Butterfly
                {
                    const WAS_CATERPILLAR = true;
                }
                EOT
        ];
        yield 'Renders a trait with methods' => [
                new TraitPrototype(
                    'Butterfly',
                    Properties::empty(),
                    Constants::empty(),
                    Methods::fromMethods([
                        new Method('wings'),
                    ])
                ),
                <<<'EOT'
                    trait Butterfly
                    {
                        public function wings()
                        {
                        }
                    }
                    EOT
        ];

        yield 'Renders a trait method with a body' => [
            new TraitPrototype(
                'Butterfly',
                Properties::empty(),
                Constants::empty(),
                Methods::fromMethods([
                    new Method(
                        'hello',
                        null,
                        Parameters::empty(),
                        ReturnType::none(),
                        Docblock::none(),
                        0,
                        MethodBody::fromLines([
                            Line::fromString('$this->foobar = $barfoo;'),
                        ])
                    ),
                ])
            ),
            <<<'EOT'
                trait Butterfly
                {
                    public function hello()
                    {
                        $this->foobar = $barfoo;
                    }
                }
                EOT
        ];
    }

    public function testFromBuilder(): void
    {
        $expected = <<<'EOT'
            <?php

            namespace Animals;

            use Measurements\Height;

            interface Animal
            {
                public function sleep();
            }

            trait Oryctolagus
            {
                /**
                 * @var bool
                 */
                private $domesticated = true;

                public function burrow(Depth $depth = 'deep')
                {
                }
            }

            class Rabbits extends Leporidae implements Animal
            {
                /**
                 * @var int
                 */
                private $force = 5;

                public $guile;

                /**
                 * All the world will be your enemy, prince with a thousand enemies
                 */
                public function jump(Height $how = 'high')
                {
                }

                public function bark(int $volume)
                {
                }
            }
            EOT
        ;
        $source = $builder = SourceCodeBuilder::create()
            ->namespace('Animals')
            ->use('Measurements\\Height')
            ->class('Rabbits')
                ->extends('Leporidae')
                ->implements('Animal')
                ->property('force')
                    ->visibility('private')
                    ->type('int')
                    ->defaultValue(5)
                ->end()
                ->property('guile')->end()
                ->method('jump')
                    ->docblock('All the world will be your enemy, prince with a thousand enemies')
                    ->parameter('how')
                        ->defaultValue('high')
                        ->type('Height')
                    ->end()
                ->end()
                ->method('bark')
                    ->parameter('volume')
                        ->type('int')
                    ->end()
                ->end()
            ->end()
            ->interface('Animal')
                ->method('sleep')->end()
            ->end()
            ->trait('Oryctolagus')
                ->property('domesticated')
                    ->visibility('private')
                    ->defaultValue(true)
                    ->type('bool')
                ->end()
                ->method('burrow')
                    ->parameter('depth')
                        ->type('Depth')
                        ->defaultValue('deep')
                    ->end()
                ->end()
            ->end()
            ->build();

        $code = $this->renderer()->render($source);

        $this->assertEquals($expected, (string) $code);
    }

    public function testConstantsAndProperties(): void
    {
        $expected = <<<'EOT'
            <?php

            namespace Animals;

            interface Animal
            {
                public function sleep();
            }

            class Rabbits implements Animal
            {
                const LEGS = 4;
                const SKIN = 'soft';

                /**
                 * @var int
                 */
                private $force = 5;

                public $guile;
            }
            EOT
        ;
        $source = $builder = SourceCodeBuilder::create()
            ->namespace('Animals')
            ->class('Rabbits')
                ->implements('Animal')
                ->property('force')
                    ->visibility('private')
                    ->type('int')
                    ->defaultValue(5)
                ->end()
                ->property('guile')->end()
                ->constant('LEGS', 4)->end()
                ->constant('SKIN', 'soft')->end()
            ->end()
            ->interface('Animal')
                ->method('sleep')->end()
            ->end()
            ->build();

        $code = $this->renderer()->render($source);

        $this->assertEquals($expected, (string) $code);
    }

    abstract protected function renderer(): Renderer;
}
