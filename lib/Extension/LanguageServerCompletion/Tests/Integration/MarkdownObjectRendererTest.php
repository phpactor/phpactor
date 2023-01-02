<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Integration;

use Closure;
use Generator;
use Phpactor\Extension\LanguageServerCompletion\Tests\IntegrationTestCase;
use Phpactor\Extension\LanguageServerHover\Renderer\HoverInformation;
use Phpactor\Extension\LanguageServerHover\Renderer\MemberDocblock;
use Phpactor\Extension\LanguageServerHover\Twig\TwigFunctions;
use Phpactor\Extension\ObjectRenderer\ObjectRendererBuilder;
use Phpactor\ObjectRenderer\Model\ObjectRenderer;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Twig\Environment;

class MarkdownObjectRendererTest extends IntegrationTestCase
{
    private Reflector $reflector;

    private ObjectRenderer $renderer;

    private SourceCodeLocator $locator;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->mkdir('project');
        $this->locator = new StubSourceLocator(ReflectorBuilder::create()->build(), $this->workspace()->path('project'), $this->workspace()->path('cache'));
        $this->reflector = ReflectorBuilder::create()
            ->addLocator($this->locator)
            ->addMemberProvider(new DocblockMemberProvider())
            ->enableContextualSourceLocation()
            ->build();
        $this->renderer = ObjectRendererBuilder::create()
             ->addTemplatePath(__DIR__ .'/../../../../../templates/help/markdown')
             ->enableInterfaceCandidates()
             ->enableAncestoralCandidates()
             ->configureTwig(function (Environment $env) {
                 (new TwigFunctions())->configure($env);
                 return $env;
             })
              ->build();
    }

    /**
     * @dataProvider provideHoverInformation
     * @dataProvider provideClass
     * @dataProvider provideInterface
     * @dataProvider provideMethod
     * @dataProvider provideVariable
     * @dataProvider provideProperty
     * @dataProvider provideConstant
     * @dataProvider provideEnum
     * @dataProvider provideEnumCase
     * @dataProvider provideTrait
     * @dataProvider provideFunction
     * @dataProvider provideSymbolOffset
     * @dataProvider provideDeclaredConstant
     * @dataProvider provideType
     * @dataProvider provideMemberDocblock
     */
    public function testRender(string $manifest, Closure $objectFactory, string $expected, bool $capture = false): void
    {
        $this->workspace()->loadManifest($manifest);

        $object = $objectFactory($this->reflector);
        $path = __DIR__ . '/expected/'. $expected;

        if (!file_exists($path)) {
            file_put_contents($path, '');
        }

        $actual = $this->renderer->render($object);

        if ($capture) {
            fwrite(STDOUT, sprintf("\nCaptured %s\n\n>>> START\n%s\n<<< END", $path, $actual));
            file_put_contents($path, $actual);
        }

        self::assertEquals(trim(file_get_contents($path)), trim($actual));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideHoverInformation(): Generator
    {
        yield 'empty' => [
            '',
            function (Reflector $reflector) {
                return new HoverInformation('', '', $reflector->reflectClassesIn('<?php class Foobar {}')->first());
            },
            'hover_information1.md',
        ];

        yield 'title no docs' => [
            '',
            function (Reflector $reflector) {
                return new HoverInformation('This is my title', '', $reflector->reflectClassesIn('<?php class Foobar {}')->first());
            },
            'hover_information2.md',
        ];

        yield 'title with docs' => [
            '',
            function (Reflector $reflector) {
                return new HoverInformation('This is my title', 'There are my docs', $reflector->reflectClassesIn('<?php class Foobar {}')->first());
            },
            'hover_information3.md',
        ];

        yield 'docs with HTML tags' => [
            '',
            function (Reflector $reflector) {
                return new HoverInformation('This is my title', '<p>There are my docs</p>', $reflector->reflectClassesIn('<?php class Foobar {}')->first());
            },
            'hover_information3.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideClass(): Generator
    {
        yield 'simple class' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn('<?php class Foobar {}')->first();
            },
            'class_reflection1.md'
        ];

        yield 'complex class' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        interface DoesThis
                        {
                        }
                        interface DoesThat
                        {
                        }
                        abstract class SomeAbstract
                        {
                        }

                        class Concrete extends SomeAbstract implements DoesThis, DoesThat
                        {
                            public function __construct(string $foo) {}
                            /**
                             * @param string|bool|null $bar
                             */
                            public function foobar(string $foo, $bar): SomeAbstract;
                        }
                        EOT
                )->get('Concrete');
            },
            'class_reflection2.md',
        ];

        yield 'class with constants and properties' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class SomeClass
                        {
                            public const FOOBAR = 'bar';
                            private const NO= 'none';
                            public $foo = 'zed';
                            public function foobar(): void {}
                        }
                        EOT
                )->get('SomeClass');
            },
            'class_reflection3.md',
        ];

        yield 'final class' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn('<?php final class Foobar {}')->first();
            },
            'class_reflection4.md',
        ];

        yield 'class that extends itself' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn('<?php class Foobar extends Foobar {}')->first();
            },
            'class_reflection5.md',
        ];

        yield 'deprecated class' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn('<?php /** @deprecated This is deprecated */class Foobar {}')->first();
            },
            'class_reflection6.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideInterface(): Generator
    {
        yield 'complex interface' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        interface DoesThis
                        {
                        }
                        interface DoesThat
                        {
                        }

                        /**
                         * Hello documentation
                         */
                        interface AwesomeInterface extends DoesThis, DoesThat
                        {
                            const FOOBAR = "BARFOO";
                            public function foo(): string;
                        }
                        EOT
                )->get('AwesomeInterface');
            },
            'interface_reflection1.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTrait(): Generator
    {
        yield 'simple trait' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        trait Blah
                        {
                            public function foo();
                        }
                        EOT
                )->get('Blah');
            },
            'trait1.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMethod(): Generator
    {
        yield 'simple' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        /**
                         * Hello documentation
                         */
                        class OneClass
                        {
                            public function foo();
                        }
                        EOT
                )->first()->methods()->get('foo');
            },
            'method1.md',
        ];

        yield 'complex method' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            /**
                             * This is my method
                             *
                             * @param bool|string $foo
                             * @param Foobar[] $zed
                             */
                            public function foo(string $bar, $foo, array $zed): void;
                        }
                        EOT
                )->first()->methods()->get('foo');
            },
            'method2.md',
        ];

        yield 'private method' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            private function foo(): void;
                        }
                        EOT
                )->first()->methods()->get('foo');
            },
            'method3.md',
        ];

        yield 'static and abstract method' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            abstract public static function foo()
                        }
                        EOT
                )->first()->methods()->get('foo');
            },
            'method4.md',
        ];

        yield 'virtual method' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        /**
                         * @method string foobar()
                         */
                        class OneClass
                        {
                        }
                        EOT
                )->first()->methods()->get('foobar');
            },
            'method5.md',
        ];

        yield 'overridden method' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class ParentClass
                        {
                            public function foobar()
                            {
                            }
                        }

                        class OneClass extends ParentClass
                        {
                            public function foobar()
                            {
                            }
                        }
                        EOT
                )->get('OneClass')->methods()->get('foobar');
            },
            'method6.md',
        ];

        yield 'overridden method from interface' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        interface ClassInterface
                        {
                            public function foobar()
                            {
                            }
                        }

                        class OneClass implements ClassInterface
                        {
                            public function foobar()
                            {
                            }
                        }
                        EOT
                )->get('OneClass')->methods()->get('foobar');
            },
            'method7.md',
        ];

        yield 'deprecated method' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            /**
                             * @deprecated Do not use me
                             */
                            public function foobar()
                            {
                            }
                        }
                        EOT
                )->get('OneClass')->methods()->get('foobar');
            },
            'method8.md'
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideProperty(): Generator
    {
        yield 'simple property' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            public $foobar;
                        }
                        EOT
                )->first()->properties()->get('foobar');
            },
            'property1.md',
        ];

        yield 'complex property' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            /**
                             * @var Foobar|string
                             */
                            public $foobar = "bar";
                        }
                        EOT
                )->first()->properties()->get('foobar');
            },
            'property2.md',
        ];

        yield 'typed property' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            public string $foobar = "bar";
                        }
                        EOT
                )->first()->properties()->get('foobar');
            },
            'property3.md',
        ];

        yield 'virtual property' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        /**
                         * @property string $foobar
                         */
                        class OneClass
                        {
                        }
                        EOT
                )->first()->properties()->get('foobar');
            },
            'property4.md',
        ];

        yield 'mixed property' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            /**
                             * @var mixed|foo
                             */
                            public $foobar;
                        }
                        EOT
                )->first()->properties()->get('foobar');
            },
            'property5.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEnum(): Generator
    {
        if (!defined('T_ENUM')) {
            return;
        }

        yield 'enum' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        enum Foobar
                        {
                            case FOOBAR;
                        }
                        EOT
                )->first();
            },
            'enum.md',
        ];

        yield 'backed enum' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        enum Foobar: string
                        {
                            case FOOBAR = "bar";
                        }
                        EOT
                )->first();
            },
            'backed_enum.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEnumCase(): Generator
    {
        if (!defined('T_ENUM')) {
            return;
        }

        yield 'enum case' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        enum Foobar
                        {
                            case FOOBAR;
                        }
                        EOT
                )->first()->cases()->get('FOOBAR');
            },
            'enum_case1.md',
        ];

        yield 'backed enum case' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        enum Foobar: string
                        {
                            case FOOBAR = 'foo';
                        }
                        EOT
                )->first()->cases()->get('FOOBAR');
            },
            'enum_backed_case1.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConstant(): Generator
    {
        yield 'simple constant' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            const FOOBAR = "barfoo";
                        }
                        EOT
                )->first()->constants()->get('FOOBAR');
            },
            'constant1.md',
        ];

        yield 'complex constant' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            private const FOOBAR = ['one', 2];
                        }
                        EOT
                )->first()->constants()->get('FOOBAR');
            },
            'constant2.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFunction(): Generator
    {
        yield 'simple function' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectFunctionsIn(
                    <<<'EOT'
                        <?php
                        function one() {}
                        EOT
                )->first();
            },
            'function1.md',
        ];

        yield 'complex function' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectFunctionsIn(
                    <<<'EOT'
                        <?php
                        function one(string $bar, bool $baz): stdClass {}
                        EOT
                )->first();
            },
            'function2.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideDeclaredConstant(): Generator
    {
        yield 'define constant' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectConstantsIn(
                    <<<'EOT'
                        <?php
                        define('FOO', 'bar');
                        EOT
                )->first();
            },
            'declared_constant1.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideSymbolOffset(): Generator
    {
        yield 'whitespace' => [
            '',
            function (Reflector $reflector) {
                return $reflector->reflectOffset(
                    <<<'EOT'
                        <?php
                        EOT
                    ,
                    1
                );
            },
            'offset1.md',
        ];

        yield 'var with local vars' => [
            '',
            function (Reflector $reflector) {
                $source = <<<'EOT'
                    <?php

                    $foo = 'string';
                    $bar = 1234;

                    $<>zed;

                    EOT
                ;
                [$source, $offset] = ExtractOffset::fromSource($source);
                return $reflector->reflectOffset($source, $offset);
            },
            'offset2.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideType(): Generator
    {
        yield 'mixed' => [
            '',
            function (Reflector $reflector) {
                return TypeFactory::mixed();
            },
            'type1.md',
        ];
        yield 'union' => [
            <<<PHP
                // File: project/Foo.php
                <?php class Foo {}
                // File: project/Baz.php
                <?php interface Baz {}
                // File: project/Trag.php
                <?php trait Trag {}
                PHP
            ,
            function (Reflector $reflector) {
                return TypeFactory::union(
                    TypeFactory::reflectedClass($reflector, 'Foo'),
                    TypeFactory::reflectedClass($reflector, 'Baz'),
                    TypeFactory::reflectedClass($reflector, 'Trag'),
                );
            },
            'type2.md',
        ];
        yield 'intersection' => [
            '',
            function (Reflector $reflector) {
                return TypeFactory::intersection(
                    TypeFactory::class('Foobar'),
                    TypeFactory::class('Barfoo'),
                );
            },
            'type3.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMemberDocblock(): Generator
    {
        yield 'single member with no doc' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            public function foo();
                        }
                        EOT
                )->first()->methods()->get('foo'));
            },
            'member_docblock1.md',
        ];

        yield 'single member with doc' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class OneClass
                        {
                            /**
                             * Foobar
                             */
                            public function foo();
                        }
                        EOT
                )->first()->methods()->get('foo'));
            },
            'member_docblock2.md',
        ];
        yield 'member with concrete parent doc' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        class Barfoo
                        {
                            /**
                             * Barfoo
                             */
                            public function foo();
                        }
                        class OneClass extends Barfoo
                        {
                            /**
                             * Foobar
                             */
                            public function foo();
                        }
                        EOT
                )->get('OneClass')->methods()->get('foo'));
            },
            'member_docblock3.md',
        ];

        yield 'member with multiple concrete parent doc' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php
                        class Doobar
                        {
                            /**
                             * Doobar
                             */
                            public function foo();
                        }

                        class Barfoo extends Doobar
                        {
                            /**
                             * Barfoo
                             */
                            public function foo();
                        }
                        class OneClass extends Barfoo
                        {
                            /**
                             * Foobar
                             */
                            public function foo();
                        }
                        EOT
                )->get('OneClass')->methods()->get('foo'));
            },
            'member_docblock4.md',
        ];

        yield 'member with interface parent' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        interface Dong
                        {
                            /**
                             * Foobar
                             */
                            public function foo();
                        }

                        class OneClass implements Dong
                        {
                            public function foo();
                        }
                        EOT
                )->get('OneClass')->methods()->get('foo'));
            },
            'member_docblock5.md',
        ];

        yield 'member with multiple interface parent' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        interface Bong
                        {
                            /**
                             * Bong
                             */
                            public function foo();
                        }

                        interface Dong
                        {
                            /**
                             * Foobar
                             */
                            public function foo();
                        }

                        class OneClass implements Dong, Bong
                        {
                            public function foo();
                        }
                        EOT
                )->get('OneClass')->methods()->get('foo'));
            },
            'member_docblock6.md',
        ];

        yield 'do not repeat interfaces' => [
            '',
            function (Reflector $reflector) {
                return new MemberDocblock($reflector->reflectClassesIn(
                    <<<'EOT'
                        <?php

                        interface Bong
                        {
                            /**
                             * Bong
                             */
                            public function foo();
                        }

                        interface Dong
                        {
                            /**
                             * Foobar
                             */
                            public function foo();
                        }

                        class TwoClass implements Dong, Bong
                        {
                            public function foo();
                        }

                        class OneClass extends TwoClass implements Dong, Bong
                        {
                            public function foo();
                        }
                        EOT
                )->get('OneClass')->methods()->get('foo'));
            },
            'member_docblock7.md',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideVariable(): Generator
    {
        yield 'variable:' => [
            '',
            function (Reflector $reflector) {
                $offset = $reflector->reflectOffset('<?php $foo = "bar";', 18);
                $variable = $offset->frame()->locals()->byName('foo')->first();
                return $variable;
            },
            'variable1.md',
        ];
    }
}
