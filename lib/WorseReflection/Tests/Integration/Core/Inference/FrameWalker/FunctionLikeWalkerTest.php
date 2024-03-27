<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\Type\IterableType;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Generator;
use Phpactor\WorseReflection\Core\Inference\Frame;

class FunctionLikeWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'It returns this' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use Acme\Factory;

                class Foobar
                {
                    public function hello()
                    {
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $this->assertCount(1, $frame->locals()->byName('this'));
            $this->assertEquals('Foobar\Barfoo\Foobar', $frame->locals()->byName('this')->first()->type()->__toString());
            $this->assertEquals(false, $frame->locals()->byName('this')->first()->isProperty());
        }];

        yield 'It returns this with correct type' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use Acme\Factory;

                class Foobar
                {
                    public function hello()
                    {
                        new \ReflectionFunction(function() { <> });
                    }
                }

                EOT
        , function (Frame $frame): void {
            $this->assertCount(1, $frame->locals()->byName('this'));
            $this->assertEquals('Foobar\Barfoo\Foobar', $frame->locals()->byName('this')->first()->type()->__toString());
            $this->assertEquals(false, $frame->locals()->byName('this')->first()->isProperty());
        }];

        yield 'It returns method arguments' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use Acme\Factory;
                use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

                class Foobar
                {
                    public function hello(World $world)
                    {
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $this->assertCount(1, $frame->locals()->byName('this'));
            $this->assertEquals(
                'Foobar\Barfoo\Foobar',
                $frame->locals()->byName('this')->first()->type()->__toString()
            );
        }];

        yield 'It injects method argument with inferred types' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use Acme\Factory;
                use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

                class Foobar
                {
                    /**
                     * @param World[] $worlds
                     * @param string $many
                     */
                    public function hello(array $worlds, $many)
                    {
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $this->assertCount(1, $frame->locals()->byName('many'));
            $this->assertEquals('string', (string) $frame->locals()->byName('many')->first()->type());

            $this->assertCount(1, $frame->locals()->byName('worlds'));
            $this->assertEquals('Foobar\Barfoo\World[]', (string) $frame->locals()->byName('worlds')->first()->type());
            $type = $frame->locals()->byName('worlds')->first()->type();
            assert($type instanceof IterableType);
            $this->assertEquals('Foobar\Barfoo\World', (string) $type->iterableValueType());
        }];

        yield 'Variadic argument' => [
            <<<'EOT'
                <?php

                namespace Foobar\Barfoo;

                use Acme\Factory;
                use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

                class Foobar
                {
                    public function hello(string ...$hellos)
                    {
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $this->assertCount(1, $frame->locals()->byName('hellos'));
            $variable = $frame->locals()->byName('hellos')->first();
            $type = $variable->type();
            assert($type instanceof IterableType);
            $this->assertEquals('string', (string)$type->iterableValueType());
        }];

        yield 'Respects closure scope' => [
            <<<'EOT'
                <?php
                $foo = 'bar';

                $hello = function () {
                    $bar = 'foo';
                    <>
                };
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$bar'), 'Scoped variable exists');
                $this->assertCount(0, $frame->locals()->byName('$foo'), 'Parent scoped variable doesnt exist');
            }
                ];

        yield 'Static anonymous function has no $this or self::' => [
            <<<'EOT'
                <?php
                $foo = 'bar';

                class Foo {
                    public function baz() {
                        $hello = static function () {
                            $bar = 'foo';
                            <>
                        };
                    }
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$bar'));
                $this->assertCount(0, $frame->locals()->byName('$this'));
            }
        ];

        yield 'Injects closure parameters' => [
            <<<'EOT'
                <?php
                $foo = 'bar';

                $hello = function (Foobar $foo) {
                    <>
                };
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$foo'));
                $variable = $frame->locals()->byName('$foo')->first();
                $this->assertEquals('Foobar', $variable->type()->__toString());
            }
        ];

        yield 'Injects imported closure parent scope variables' => [
            <<<'EOT'
                <?php
                $zed = 'zed';
                $art = 'art';

                $hello = function () use ($zed) {
                    <>
                };
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $zed = $frame->locals()->byName('$zed')->first();
                $this->assertEquals('"zed"', (string) $zed->type());
            }
        ];

        yield 'Incomplete use name' => [
            <<<'EOT'
                <?php
                function () use ($<>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(0, $frame->locals());
            }
        ];

        yield 'Injects variables with @var (non-standard)' => [
            <<<'EOT'
                <?php
                /** @var string $zed */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('string', (string) $frame->locals()->byName('$zed')->last()->type());
            }
        ];
    }
}
