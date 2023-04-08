<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateReturnTypeTransformer;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\WorseReflection\Reflector;
use function Amp\Promise\wait;

class UpdateReturnTypeTransformerTest extends WorseTestCase
{
    /**
     * @dataProvider provideTransform
     */
    public function testTransform(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $this->workspace()->put(
            'Example.php',
            '<?php namespace Namespaced; class NsTest { /** @return Baz[] */public function bazes(): array {}} class Baz{}'
        );
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = $this->createTransformer($reflector);
        $transformed = wait($transformer->transform($source))->apply($source);
        self::assertEquals($expected, $transformed);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTransform(): Generator
    {
        yield 'add missing return type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    private function array()
                    {
                        return ['string' => new Baz'];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {
                    private function array(): array
                    {
                        return ['string' => new Baz'];
                    }
                }
                EOT
        ];

        yield 'add generator return type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    private function array()
                    {
                        yield 'foo';
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {
                    private function array(): Generator
                    {
                        yield 'foo';
                    }
                }
                EOT
        ];

        yield 'add nullable return type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    private function array()
                    {
                        if ($foo) {
                            return null;
                        }
                        return ['string' => new Baz'];
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {
                    private function array(): ?array
                    {
                        if ($foo) {
                            return null;
                        }
                        return ['string' => new Baz'];
                    }
                }
                EOT
        ];

        yield 'add multipe return types' => [
            <<<'EOT'
                <?php

                class Foobar {
                    private function foo()
                    {
                        return 'string';
                    }

                    private function baz()
                    {
                        return 10;
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {
                    private function foo(): string
                    {
                        return 'string';
                    }

                    private function baz(): int
                    {
                        return 10;
                    }
                }
                EOT
        ];
        yield 'do not add missing type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    private function foo()
                    {
                        return $this->baz();
                    }

                    private function baz()
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {
                    private function foo()
                    {
                        return $this->baz();
                    }

                    private function baz(): void
                    {
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
        $diagnostics = array_map(fn (Diagnostic $d) => $d->message(), iterator_to_array(wait($transformer->diagnostics($source))));
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

        yield 'method with return type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz(): array
                    {
                        return 'string';
                    }
                }
                EOT
            ,
            [
            ]
        ];

        yield 'method with no method body' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz()
                    {
                    }
                }
                EOT
            ,
            [
                'Missing return type `void`',
            ]
        ];

        yield 'diagnostics for missing return type' => [
            <<<'EOT'
                <?php

                class Foobar {
                    public function baz()
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
                'Missing return type `array`',
            ]
        ];

        yield 'ignores constructor' => [
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

        yield 'ignores missing return type with mixed' => [
            <<<'EOT'
                <?php

                class Foobar {
                    /**
                     * @return mixed
                     */
                    public function foo()
                    {
                    }
                }
                EOT
            ,
            [
            ]
        ];

        yield 'ignores template type' => [
            <<<'EOT'
                <?php

                /**
                 * @template T
                 */
                class Foobar {
                    /**
                     * @var T
                     */
                    private $item;

                    /**
                     * @return T
                     */
                    public function foo()
                    {
                        return $this->item;
                    }
                }
                EOT
            ,
            [
            ]
        ];

        yield 'never on interface' => [
            <<<'EOT'
                <?php

                interface Foobar {
                    public function foo();
                }
                EOT
            ,
            [
            ]
        ];

        yield 'never on abstract' => [
            <<<'EOT'
                <?php

                abstract class Foobar {
                    abstract public function foo();
                }
                EOT
            ,
            [
            ]
        ];
    }

    private function createTransformer(Reflector $reflector): UpdateReturnTypeTransformer
    {
        return new UpdateReturnTypeTransformer(
            $reflector,
            $this->updater(),
            $this->builderFactory($reflector)
        );
    }
}
