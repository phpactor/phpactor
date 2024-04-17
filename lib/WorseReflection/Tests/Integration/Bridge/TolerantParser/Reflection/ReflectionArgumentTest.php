<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\TestUtils\ExtractOffset;
use Closure;

class ReflectionArgumentTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionMethod
     */
    public function testReflectMethodCall(string $source, array $frame, Closure $assertion): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $reflection = self::createReflector($source)->reflectMethodCall(TextDocumentBuilder::create($source)->build(), $offset);
        $assertion($reflection->arguments());
    }

    /**
     * @return Generator<string,array{string,array,Closure(Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection): void}>
     */
    public static function provideReflectionMethod():Generator
    {
        yield 'It guesses the name from the var name' => [
            <<<'EOT'
                <?php

                $foo->b<>ar($foo);
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals('foo', $arguments->first()->guessName());
            },
        ];
        yield 'It returns a named argument' => [
            <<<'EOT'
                <?php

                $foo->b<>ar(foo: 'hello');
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals('"hello"', $arguments->get('foo')->type()->__toString());
            },
        ];
        yield 'It returns node context' => [
            <<<'EOT'
                <?php

                $foo->b<>ar(foo: 'hello');
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertInstanceOf(NodeContext::class, $arguments->get('foo')->nodeContext());
            },
        ];
        yield 'It guesses the name from return type' => [
            <<<'EOT'
                <?php

                class AAA
                {
                    public function bob(): Alice
                    {
                    }
                }

                $foo = new AAA();
                $foo->b<>ar($foo->bob());
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals('alice', $arguments->first()->guessName());
            },
        ];
        yield 'It returns a generated name if it cannot be determined' => [
            <<<'EOT'
                <?php

                class AAA
                {
                }

                $foo = new AAA();
                $foo->b<>ar($foo->bob(), $foo->zed());
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals('argument0', $arguments->first()->guessName());
                self::assertEquals('argument1', $arguments->last()->guessName());
            },
        ];
        yield 'It returns the argument type' => [
            <<<'EOT'
                <?php

                $integer = 1;
                $foo->b<>ar($integer);
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals('1', (string) $arguments->first()->type());
            },
        ];
        yield 'It returns the value' => [
            <<<'EOT'
                <?php

                $integer = 1;
                $foo->b<>ar($integer);
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals(1, $arguments->first()->value());
            },
        ];
        yield 'It returns the position' => [
            <<<'EOT'
                <?php

                $foo->b<>ar($integer);
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals(17, $arguments->first()->position()->start()->toInt());
                self::assertEquals(25, $arguments->first()->position()->end()->toInt());
            },
        ];
        yield 'It infers named parameters' => [
            <<<'EOT'
                <?php

                $foo->b<>ar(foo: $integer);
                EOT
            , [
            ],
            function (ReflectionArgumentCollection $arguments): void {
                self::assertEquals('foo', $arguments->first()->guessName());
            },
        ];
    }
}
