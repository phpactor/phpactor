<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Adapter\DocblockParser\ParserDocblockUpdater;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockTransformer;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockTransformerTest extends WorseTestCase
{
    /**
     * @dataProvider provideUpdateReturn
     */
    public function testUpdateReturn(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $this->workspace()->put(
            'Example.php',
            '<?php namespace Namespaced; class NsTest { /** @return Baz[] */public function bazes(): array {}} class Baz{}'
        );
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = $this->createTransformer($reflector);
        $transformed = $transformer->transform($source)->apply($source);
        self::assertEquals($expected, $transformed);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideUpdateReturn(): Generator
    {
        yield 'add missing docblock' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return $this->array();
                    }

                    /** @return array<string,Baz> */
                    private function array(): array
                    {
                        return ['string' => new Baz'];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return array<string,Baz>
                     */
                    public function baz(): array
                    {
                        return $this->array();
                    }

                    /** @return array<string,Baz> */
                    private function array(): array
                    {
                        return ['string' => new Baz'];
                    }
                }
                EOT
        ];

        yield 'add array literal' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return [
                            'foo' => 'bar',
                            'baz' => 'boo',
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return array<string,string>
                     */
                    public function baz(): array
                    {
                        return [
                            'foo' => 'bar',
                            'baz' => 'boo',
                        ];
                    }
                }
                EOT
        ];

        yield 'add union of array literals' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        if ($foo) {
                            return [
                                'baz' => 'bar',
                            ];
                        }

                        return [
                            'foo' => 'bar',
                            'baz' => 'boo',
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return array<string,string>
                     */
                    public function baz(): array
                    {
                        if ($foo) {
                            return [
                                'baz' => 'bar',
                            ];
                        }

                        return [
                            'foo' => 'bar',
                            'baz' => 'boo',
                        ];
                    }
                }
                EOT
        ];

        yield 'permit wider return types' => [
            <<<'EOT'
                <?php

                abstract class Foo {}
                class ConcreteFoo extends Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        return new ConcreteFoo();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                abstract class Foo {}
                class ConcreteFoo extends Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        return new ConcreteFoo();
                    }
                }
                EOT
        ];

        yield 'permit wider return types in union' => [
            <<<'EOT'
                <?php

                abstract class Foo {}
                class ConcreteFoo extends Foo {}
                class Baz extends Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        if ($bar) {
                            return new Baz();
                        }
                        return new ConcreteFoo();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                abstract class Foo {}
                class ConcreteFoo extends Foo {}
                class Baz extends Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        if ($bar) {
                            return new Baz();
                        }
                        return new ConcreteFoo();
                    }
                }
                EOT
        ];

        yield 'but adds generic types' => [
            <<<'EOT'
                <?php

                abstract class Foo {}
                class ConcreteFoo extends Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        /** @var ConcreteFoo<Baz> */
                        $foo;
                        return $foo;
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                abstract class Foo {}
                class ConcreteFoo extends Foo {}

                class Foobar {

                    /**
                     * @return ConcreteFoo<Baz>
                     */
                    public function baz(): Foo
                    {
                        /** @var ConcreteFoo<Baz> */
                        $foo;
                        return $foo;
                    }
                }
                EOT
        ];

        yield 'and interfaces' => [
            <<<'EOT'
                <?php

                interface Foo {}
                class ConcreteFoo implements Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        return new ConcreteFoo();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Foo {}
                class ConcreteFoo implements Foo {}

                class Foobar {
                    public function baz(): Foo
                    {
                        return new ConcreteFoo();
                    }
                }
                EOT
        ];

        yield 'add generator' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz()
                    {
                        yield 'foo';
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return Generator<string>
                     */
                    public function baz()
                    {
                        yield 'foo';
                    }
                }
                EOT
        ];

        yield 'add generator with array value' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function t5()
                    {
                        yield 'foo' => [
                            'val',
                            new stdClass(),
                        ];
                        yield 'bar' => [
                            'lav',
                            new stdClass(),
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return Generator<string,array{string,stdClass}>
                     */
                    public function t5()
                    {
                        yield 'foo' => [
                            'val',
                            new stdClass(),
                        ];
                        yield 'bar' => [
                            'lav',
                            new stdClass(),
                        ];
                    }
                }
                EOT
        ];

        yield 'adds docblock for array' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return array_map(fn () => null, []);
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return null[]
                     */
                    public function baz(): array
                    {
                        return array_map(fn () => null, []);
                    }
                }
                EOT
        ];

        yield 'does not add non-array return type when array return is given' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return $this->foo();
                    }

                    /**
                     * @return mixed
                     */
                    private function foo() {}
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return $this->foo();
                    }

                    /**
                     * @return mixed
                     */
                    private function foo() {}
                }
                EOT
        ];

        yield 'adds docblock for closure' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): Closure
                    {
                        return function (string $foo): int {};
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return Closure(string): int
                     */
                    public function baz(): Closure
                    {
                        return function (string $foo): int {};
                    }
                }
                EOT
        ];

        yield 'adds docblock for invoked closure' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz()
                    {
                        return (function (string $foo): int {return 12;})();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @return int
                     */
                    public function baz()
                    {
                        return (function (string $foo): int {return 12;})();
                    }
                }
                EOT
        ];

        yield 'imports classes' => [
            <<<'EOT'
                <?php

                use Namespaced\NsTest;

                class Foobar {
                    public function baz(): array
                    {
                        return (new NsTest())->bazes();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use Namespaced\Baz;
                use Namespaced\NsTest;

                class Foobar {

                    /**
                     * @return Baz[]
                     */
                    public function baz(): array
                    {
                        return (new NsTest())->bazes();
                    }
                }
                EOT
        ];

        yield 'inherited type' => [
            <<<'EOT'
                <?php

                abstract class Foobag {
                    /**
                     * @return Baz[]
                     */
                    public function baz(): array {
                        return [];
                    }
                }

                class Foobar extends Foobag {
                    public function baz(): array
                    {
                        return [new Baz()];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                abstract class Foobag {
                    /**
                     * @return Baz[]
                     */
                    public function baz(): array {
                        return [];
                    }
                }

                class Foobar extends Foobag {
                    public function baz(): array
                    {
                        return [new Baz()];
                    }
                }
                EOT
        ];

        yield 'namespaced array shape' => [
            <<<'EOT'
                <?php

                namespace Foo;

                class Foobar {
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Foo;

                use Generator;

                class Foobar {

                    /**
                     * @return Generator<array{string,Closure(Bar): string}>
                     */
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
        ];

        yield 'trait' => [
            <<<'EOT'
                <?php

                namespace Foo;

                trait Foobar {
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Foo;

                use Generator;

                trait Foobar {

                    /**
                     * @return Generator<array{string,Closure(Bar): string}>
                     */
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
        ];

        yield 'updates existing docblock' => [
            <<<'EOT'
                <?php

                namespace Foo;

                trait Foobar {
                    /**
                     *
                     */
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Foo;

                use Generator;

                trait Foobar {
                    /**
                     *
                     * @return Generator<array{string,Closure(Bar): string}>
                     */
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
        ];

        yield 'updates existing docblock with other tags' => [
            <<<'EOT'
                <?php

                namespace Foo;

                trait Foobar {
                    /**
                     * @author Daniel Leech
                     */
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Foo;

                use Generator;

                trait Foobar {
                    /**
                     * @author Daniel Leech
                     * @return Generator<array{string,Closure(Bar): string}>
                     */
                    public function baz(): array
                    {
                        yield [
                            'foobar',
                            function (Bar $b): string {
                            }
                        ];
                    }
                }
                EOT
        ];
    }

    /**
     * @dataProvider provideDiagnostics
     * @param string[] $expected
     */
    public function testDiagnostics(string $example, array $expected): void
    {
        $source = SourceCode::fromString($example);
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = $this->createTransformer($reflector);
        $diagnostics = array_map(fn (Diagnostic $d) => $d->message(), iterator_to_array($transformer->diagnostics($source)));
        self::assertEquals($expected, $diagnostics);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideDiagnostics(): Generator
    {
        yield 'no methods' => [
            <<<'EOT'
                <?php

                class Foobar {
                }
                EOT
            ,
            [
            ]
        ];

        yield 'ignore constructor' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function __construct()
                    {
                    }
                }
                EOT
            ,
            [
            ]
        ];

        yield 'missing return type corresponds to method return type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): string
                    {
                        return 'string';
                    }
                }
                EOT
            ,
            [
            ]
        ];

        yield 'diagnostics for missing docblock' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return $this->array();
                    }

                    /** @return array<string,Baz> */
                    private function array(): array
                    {
                        return ['string' => new Baz'];
                    }
                }
                EOT
            ,
            [
                'Missing @return array<string,Baz>',
            ]
        ];
    }

    private function createTransformer(Reflector $reflector): UpdateDocblockTransformer
    {
        return new UpdateDocblockTransformer(
            $reflector,
            $this->updater(),
            $this->builderFactory($reflector),
            new ParserDocblockUpdater(DocblockParser::create())
        );
    }
}
