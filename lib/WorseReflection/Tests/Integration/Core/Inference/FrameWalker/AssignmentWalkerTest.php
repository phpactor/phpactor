<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\IterableType;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class AssignmentWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'It registers string assignments' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $foobar = 'foobar';
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $this->assertCount(1, $frame->locals()->byName('foobar'));
            $symbolInformation = $frame->locals()->byName('foobar')->first()->symbolContext();
            $this->assertEquals('string', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];
        yield 'It returns types for reassigned variables' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello(World $world = 'test')
                    {
                        $foobar = $world;
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('World', (string) $symbolInformation->type());
            $this->assertEquals('test', (string) $symbolInformation->value());
        }];

        yield 'It returns type for $this' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello(World $world)
                    {
                        <>
                    }
                }

                EOT
        , function (Frame $frame): void {
            $vars = $frame->locals()->byName('this');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('Foobar', (string) $symbolInformation->type());
        }];

        yield 'It tracks assigned properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello(Barfoo $world)
                    {
                        $this->foobar = 'foobar';
                        <>
                    }
                }
                EOT
        , function (Frame $frame): void {
            $vars = $frame->properties()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('string', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];

        yield 'It assigns property values to assignments' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /** @var Foobar[] */
                    private $foobar;

                    public function hello(Barfoo $world)
                    {
                        $foobar = $this->foobar;
                        <>
                    }
                }
                EOT
        , function (Frame $frame): void {
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $type = $symbolInformation->type();
            assert($type instanceof IterableType);
            $this->assertEquals('Foobar[]', (string) $type);
            $this->assertEquals('Foobar', (string) $symbolInformation->type()->valueType);
        }];


        yield 'It tracks assigned array properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $this->foobar[] = 'foobar';
                        <>
                    }
                }
                EOT
        , function (Frame $frame): void {
            $vars = $frame->properties()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('array', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];

        yield 'It tracks assigned from variable' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello(Barfoo $world)
                    {
                        $foobar = 'foobar';
                        $this->$foobar = 'foobar';
                        <>
                    }
                }
                EOT
        , function (Frame $frame): void {
            $vars = $frame->properties()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('string', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];

        yield 'Handles array assignments' => [
            <<<'EOT'
                <?php
                $foo = [ 'foo' => 'bar' ];
                $bar = $foo['foo'];
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('array', (string) $frame->locals()->first()->symbolContext()->type());
                $this->assertEquals(['foo' => 'bar'], $frame->locals()->first()->symbolContext()->value());
                $this->assertEquals('string', (string) $frame->locals()->last()->symbolContext()->type());
                $this->assertEquals('bar', (string) $frame->locals()->last()->symbolContext()->value());
            }
        ];

        yield 'Includes list assignments' => [
            <<<'EOT'
                <?php
                list($foo, $bar) = [ 'foo', 'bar' ];
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->first()->symbolContext()->value());
                $this->assertEquals('string', (string) $frame->locals()->first()->symbolContext()->type());
            }
        ];

        yield 'New list assignment' => [
            <<<'EOT'
                <?php
                [$foo, $bar] = [ 'foo', 'bar' ];
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->atIndex(0)->symbolContext()->value());
                $this->assertEquals('bar', $frame->locals()->atIndex(1)->symbolContext()->value());
            }
        ];

        yield 'From return type with docblock' => [
            <<<'EOT'
                <?php

                namespace Foobar;

                use Foo\Lister;

                interface Barfoo
                {
                    /**
                     * @return Lister<Collection>
                     */
                    public static function bar(): List;
                }

                class Baz
                {
                    public function (Barfoo $barfoo)
                    {
                        $bar = $barfoo->bar();
                        <>
                    }
                }
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(3, $frame->locals());
                $this->assertEquals(
                    'Foo\Lister<Foobar\Collection>',
                    (string) $frame->locals()->byName('bar')->first()->symbolContext()->types()->best()
                );
                $type = $frame->locals()->byName('bar')->first()->symbolContext()->types()->best();
                $this->assertEquals(
                    'Foobar\Collection',
                    $type->iterableValueType()->__toString()
                );
            }
        ];

        yield 'From incomplete assignment' => [
            <<<'EOT'
                <?php

                class Baz
                {
                    public function function1(Barfoo $barfoo)
                    {
                        $barfoo = $barfoo->as<>
                    }

                    public function function2(): Baz;
                }
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('barfoo'));
                $type = $frame->locals()->byName('barfoo')->first()->symbolContext()->types()->best();
                assert($type instanceof ClassType);
                $this->assertEquals('Barfoo', $type->name->short());
            }
        ];

        yield 'References previously walked member' => [
            <<<'EOT'
                <?php

                class Zed
                {
                    public function foobar():string {}
                }

                class Car
                {
                    /**
                     * @var Zed
                     */
                    public $options;
                }

                class Baz
                {
                    private $foobar;
                    private $options;
                    public function __construct(Barfoo $barfoo)
                    {
                        $this->foobar = new Car();
                        $this->options = $this->foobar->options;
                    }
                    public function bar()
                    {
                        $barfoo = $this->options->foobar();
                        $barfo<>o;
                    }
                }
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('barfoo'));
            }
        ];
    }
}
