<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\IterableType;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class AssignmentWalkerTest extends FrameWalkerTestCase
{
    public static function provideWalk(): Generator
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
            $var = $frame->locals()->byName('foobar')->first();
            $this->assertEquals('"foobar"', (string) $var->type());
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
            $var = $vars->first();
            $this->assertEquals('World', (string) $var->type());
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
            $var = $vars->first();
            $this->assertEquals('Foobar', (string) $var->type());
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
            $var = $vars->first();
            $this->assertEquals('"foobar"', (string) $var->type());
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
            $var = $vars->last();
            $type = $var->type();
            assert($type instanceof IterableType);
            $this->assertEquals('Foobar[]', (string) $type);
            $this->assertEquals('Foobar', (string) $type->iterableValueType());
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
            $var = $vars->first();
            $this->assertEquals('array', (string) $var->type());
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
            $var = $vars->first();
            $this->assertEquals('"foobar"', (string) $var->type());
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
                $this->assertEquals('array{foo:"bar"}', (string) $frame->locals()->first()->type());
                $this->assertEquals('"bar"', (string) $frame->locals()->last()->type());
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
                $this->assertEquals('foo', $frame->locals()->first()->name());
                $this->assertEquals('"foo"', (string)$frame->locals()->first()->type());
                $this->assertEquals('bar', $frame->locals()->atIndex(1)->name());
                $this->assertEquals('"bar"', (string)$frame->locals()->atIndex(1)->type());
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
                $this->assertEquals('"foo"', (string)$frame->locals()->atIndex(0)->type());
                $this->assertEquals('"bar"', (string)$frame->locals()->atIndex(1)->type());
            }
        ];

        yield 'From generic return type with docblock' => [
            <<<'EOT'
                <?php

                namespace Foobar;

                /**
                 * @template T
                 * @extends \Iterator<T>
                 */
                interface Listy extends \Iterator {
                }

                interface Barfoo
                {
                    /**
                     * @return Listy<Collection>
                     */
                    public static function bar(): Listy;
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
                    'Foobar\Listy<Foobar\Collection>',
                    (string) $frame->locals()->byName('bar')->first()->type()
                );
                $type = $frame->locals()->byName('bar')->first()->type();
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
                $type = $frame->locals()->byName('barfoo')->first()->type();
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
