<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockTransformer;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockTransformerTest extends WorseTestCase
{
    /**
     * @dataProvider provideTransform
     */
    public function testTransform(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = $this->createTransformer($reflector);
        $transformed = $transformer->transform($source)->apply($source);
        self::assertEquals($expected, $transformed);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTransform(): Generator
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

        yield 'update docblock' => [
            <<<'EOT'
                <?php

                class Foobar {
                    /**
                     * @return array
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

        yield 'no update missing' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): void
                    {
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
                    public function baz(): void
                    {
                    }

                    /** @return array<string,Baz> */
                    private function array(): array
                    {
                        return ['string' => new Baz'];
                    }
                }
                EOT
        ];

        yield 'update array literal' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): void
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
                    public function baz(): void
                    {
                        return [
                            'foo' => 'bar',
                            'baz' => 'boo',
                        ];
                    }
                }
                EOT
        ];
 
        yield 'update union of array literals' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): void
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
                    public function baz(): void
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
                'Method "baz" returns `array<string,Baz>` but return type is `array`',
            ]
        ];
    }

    private function createTransformer(Reflector $reflector): UpdateDocblockTransformer
    {
        return new UpdateDocblockTransformer(
            $reflector,
            $this->updater(),
            $this->builderFactory($reflector),
            DocblockParser::create()
        );
    }
}
