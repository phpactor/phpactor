<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\ImplementContracts;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use function Amp\Promise\wait;

class ImplementContractsTest extends WorseTestCase
{
    #[DataProvider('provideCompleteConstructor')]
    public function testImplementContracts(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = new ImplementContracts($reflector, $this->updater(), $this->builderFactory($reflector));
        $transformed = wait($transformer->transform($source));
        $this->assertEquals((string) $expected, (string) $transformed->apply($source));
    }

    /**
     * @return Generator<string,array<int,string>>
     */
    public static function provideCompleteConstructor(): Generator
    {
        yield 'It does nothing on source with no classes' => [
            <<<'EOT'
                <?php
                EOT
            ,
            <<<'EOT'
                <?php
                EOT

        ];
        yield 'It does nothing on class with no interfaces or parent classes' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar
                {
                }
                EOT

        ];
        yield 'It implements an interface' => [
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public function dig(int $depth = 5);
                }

                class Foobar implements Rabbit
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public function dig(int $depth = 5);
                }

                class Foobar implements Rabbit
                {
                    public function dig(int $depth = 5)
                    {
                    }
                }
                EOT

        ];
        yield 'It implements a static methods' => [
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public static function dig(int $depth = 5): Dirt;
                }

                class Foobar implements Rabbit
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public static function dig(int $depth = 5): Dirt;
                }

                class Foobar implements Rabbit
                {
                    public static function dig(int $depth = 5): Dirt
                    {
                    }
                }
                EOT

        ];
        yield 'It implements multiple interfaces' => [
            <<<'EOT'
                <?php

                interface Dog
                {
                    public function bark(int $volume = 11): Sound
                }

                interface Rabbit
                {
                    public function dig(int $depth = 5): Dirt
                }

                class Foobar implements Rabbit, Dog
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Dog
                {
                    public function bark(int $volume = 11): Sound
                }

                interface Rabbit
                {
                    public function dig(int $depth = 5): Dirt
                }

                class Foobar implements Rabbit, Dog
                {
                    public function dig(int $depth = 5): Dirt
                    {
                    }

                    public function bark(int $volume = 11): Sound
                    {
                    }
                }
                EOT

        ];
        yield 'It does adds inherit docblocks' => [
            <<<'EOT'
                <?php

                interface Bird
                {
                    /**
                     * Emit chirping sound.
                     */
                    public function chirp();
                }

                class Foobar implements Bird
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Bird
                {
                    /**
                     * Emit chirping sound.
                     */
                    public function chirp();
                }

                class Foobar implements Bird
                {
                    public function chirp()
                    {
                    }
                }
                EOT

        ];
        yield 'It is idempotent' => [
            <<<'EOT'
                <?php

                interface Bird
                {
                    public function chirp();
                }

                class Foobar implements Bird
                {
                    public function chirp() {}
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Bird
                {
                    public function chirp();
                }

                class Foobar implements Bird
                {
                    public function chirp() {}
                }
                EOT
        ];
        yield 'It is adds after the last method' => [
            <<<'EOT'
                <?php

                interface Bird
                {
                    public function chirp();
                }

                class Foobar implements Bird
                {
                    public function hello()
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Bird
                {
                    public function chirp();
                }

                class Foobar implements Bird
                {
                    public function hello()
                    {
                    }

                    public function chirp()
                    {
                    }
                }
                EOT
        ];
        yield 'It uses the short names' => [
            <<<'EOT'
                <?php

                use Animals\Sound;

                interface Bird
                {
                    public function chirp(): Sound;
                }

                class Foobar implements Bird
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use Animals\Sound;

                interface Bird
                {
                    public function chirp(): Sound;
                }

                class Foobar implements Bird
                {
                    public function chirp(): Sound
                    {
                    }
                }
                EOT
        ];
        yield 'It implements abstract functions' => [
            <<<'EOT'
                <?php

                abstract class Bird
                {
                    abstract public function chirp();
                }

                class Foobar extends Bird
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                abstract class Bird
                {
                    abstract public function chirp();
                }

                class Foobar extends Bird
                {
                    public function chirp()
                    {
                    }
                }
                EOT
        ];
        yield 'It implements methods from abstract class which implements an interface' => [
            <<<'EOT'
                <?php

                interface Animal
                {
                    abstract public function jump();
                }

                abstract class Bird implements Animal
                {
                }

                class Foobar extends Bird
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Animal
                {
                    abstract public function jump();
                }

                abstract class Bird implements Animal
                {
                }

                class Foobar extends Bird
                {
                    public function jump()
                    {
                    }
                }
                EOT
        ];
        yield 'It ignores methods that already exist' => [
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public function dig(int $depth = 5): Dirt;

                    public function foobar();
                }

                class Foobar implements Rabbit
                {
                    public function dig(int $depth = 5): Dirt
                    {
                    }

                    public function foobar()
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public function dig(int $depth = 5): Dirt;

                    public function foobar();
                }

                class Foobar implements Rabbit
                {
                    public function dig(int $depth = 5): Dirt
                    {
                    }

                    public function foobar()
                    {
                    }
                }
                EOT
        ];
        yield 'It imports use statements outside of the current namespace' => [
            <<<'EOT'
                <?php

                interface Rabbit
                {
                    public function dig(Arg\Barg $depth = 5): Barfoo\Dirt;
                }

                class Foobar implements Rabbit
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use Arg\Barg;
                use Barfoo\Dirt;

                interface Rabbit
                {
                    public function dig(Arg\Barg $depth = 5): Barfoo\Dirt;
                }

                class Foobar implements Rabbit
                {
                    public function dig(Barg $depth = 5): Dirt
                    {
                    }
                }
                EOT
        ];
        yield 'It implements contracts with nullable return type' => [
            <<<'EOT'
                <?php

                interface Animal
                {
                    abstract public function jump(): ?Arg\Foo;
                }
                class Foobar implements Animal
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use Arg\Foo;

                interface Animal
                {
                    abstract public function jump(): ?Arg\Foo;
                }
                class Foobar implements Animal
                {
                    public function jump(): ?Foo
                    {
                    }
                }
                EOT
        ];
        yield 'It uses "iterable"' => [
            <<<'EOT'
                <?php

                interface Animal
                {
                    abstract public function jump(): iterable;
                }
                class Foobar implements Animal
                {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface Animal
                {
                    abstract public function jump(): iterable;
                }
                class Foobar implements Animal
                {
                    public function jump(): iterable
                    {
                    }
                }
                EOT
        ];
    }

    #[DataProvider('provideDiagnostics')]
    public function testDiagnostics(string $example, int $expectedCount): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new ImplementContracts(
            $this->reflectorForWorkspace($example),
            $this->updater(),
            $this->builderFactory($this->reflectorForWorkspace($example))
        );
        $this->assertCount($expectedCount, wait($transformer->diagnostics($source)));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideDiagnostics(): Generator
    {
        yield 'empty' => [
            <<<'EOT'
                <?php
                EOT
        , 0
        ];

        yield 'missing method' => [
            <<<'EOT'
                <?php

                interface A { public function barfoo(): void; }

                class B implements A
                {
                }
                EOT
        , 1
        ];

        yield 'not missing method' => [
            <<<'EOT'
                <?php

                interface A { public function barfoo(): void; }

                class B implements A
                {
                    public function barfoo() {}
                }
                EOT
        , 0
        ];

        yield 'not missing method with trait' => [
            <<<'EOT'
                <?php

                abstract class A { abstract public function barfoo(): void; }

                trait Foo {
                    public function barfoo() {};
                }

                class B extends A
                {
                    use Foo;
                }
                EOT
        , 0
        ];
    }
}
