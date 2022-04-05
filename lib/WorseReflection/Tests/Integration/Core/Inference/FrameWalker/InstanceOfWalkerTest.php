<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Generator;
use Phpactor\WorseReflection\Core\Inference\Frame;

class InstanceOfWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'infers type from instanceof' => [
            <<<'EOT'
                <?php

                if ($foobar instanceof Foobar) {
                }
                <>
                EOT
        , function (Frame $frame): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals('Foobar', (string) $frame->locals()->first()->type());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(1)->type());
        }
        ];

        yield 'removes type if return' => [
            <<<'EOT'
                <?php

                if ($foobar instanceof Foobar) {
                    return;
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(1)->type());
        }
        ];

        yield 'removes type if exception' => [
            <<<'EOT'
                <?php

                if ($foobar instanceof Foobar) {
                    throw new Exception("HAI!");
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(1)->type());
        }
        ];

        yield 'removes type after continue' => [
            <<<'EOT'
                <?php


                foreach ([1, 2] as $hello) {
                    if (!$foobar instanceof Foobar) {
                        continue;
                    }
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals()->byName('foobar'));
            $this->assertEquals('Foobar', $frame->locals()->last()->type()->__toString());
        }
        ];

        yield 'removes type after break' => [
            <<<'EOT'
                <?php


                foreach ([1, 2] as $hello) {
                    if (!$foobar instanceof Foobar) {
                        break;
                    }
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals()->byName('foobar'));
            $this->assertEquals('Foobar', $frame->locals()->last()->type()->__toString());
        }
        ];

        yield 'adds no type information if bang negated' => [
            <<<'EOT'
                <?php

                if (!$foobar instanceof Foobar) {
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(0)->type());
        }
    ];

        yield 'adds no type information if false negated' => [
            <<<'EOT'
                <?php

                if (false === $foobar instanceof Foobar) {
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(0)->type());
        }
        ];


        yield 'adds type information if negated and if statement terminates' => [
            <<<'EOT'
                <?php

                if (!$foobar instanceof Foobar) {

                    return;
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(0)->type());
            $this->assertEquals('Foobar', (string) $frame->locals()->atIndex(1)->type());
        }
                ];

        yield 'adds type information for nullable if negated and if statement terminates' => [
            <<<'EOT'
                <?php
                class fo { public static function foo(): ?Foo {}}

                $foo = fo::foo();

                if (!$foobar instanceof Foobar) {
                    throw new \RuntimeException('');
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(3, $frame->locals());
            $this->assertEquals('Foobar', (string) $frame->locals()->atIndex(2)->type());
        }
        ];

        yield 'has no type information if double negated and if statement terminates' => [
            <<<'EOT'
                <?php

                if (!!$foobar instanceof Foobar) {

                    return;
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals('Foobar', (string) $frame->locals()->atIndex(1)->type());
            $this->assertEquals(TypeFactory::unknown(), $frame->locals()->atIndex(0)->type());
        }
        ];

        yield 'will create a union type with or' => [
            <<<'EOT'
                <?php

                if ($foobar instanceof Foobar || $foobar instanceof Barfoo) {

                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals('Foobar|Barfoo', $frame->locals()->atIndex(0)->type()->__toString());
        }
        ];

        yield 'will create a union type with and' => [
            <<<'EOT'
                <?php

                if ($foobar instanceof Foobar && $foobar instanceof Barfoo) {

                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals('Foobar|Barfoo', $frame->locals()->atIndex(0)->type()->__toString());
        }
        ];

        yield 'reverts to original type' => [
            <<<'EOT'
                <?php

                $foobar = new stdClass();
                if ($foobar instanceof Foobar) {
                    return;
                }
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(3, $frame->locals());
            $this->assertEquals('stdClass', $frame->locals()->atIndex(2)->type());
        }
        ];

        yield 'resolves namespace' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;

                if ($foobar instanceof Barfoo) {
                <>
                }
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals('Foobar\Barfoo', (string) $frame->locals()->atIndex(0)->type());
        }
        ];

        yield 'ignores condition incomplete expression' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;

                if ($foobar instanceof Barfoo
                <>
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
        }
    ];

        yield 'ignores condition with missing token' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;

                if ($foobar instanceof Barfoo
                <>

                if
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(2, $frame->locals());
        }
        ];

        yield 'ignores expression which does not include a variable' => [
            <<<'EOT'
                <?php

                functoin this_function_returns_something() {
                    return new stdClass();
                }

                if (this_function_returns_something() instanceof Barfoo) {
                <>
                }
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(0, $frame->locals());
        }
        ];

        yield 'should handle properties' => [
            <<<'EOT'
                <?php

                class Foo
                {
                    private $bar;

                    public function bar(): void
                    {
                        if (!$this->bar instanceof Bar) {
                            continue;
                        }

                        <>
                    }
                }
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals('Foo', $frame->locals()->atIndex(0)->type()->__toString());
            $this->assertCount(2, $frame->properties());
            $this->assertEquals('Foo', $frame->properties()->atIndex(0)->classType()->__toString());
            $this->assertEquals(TypeFactory::unknown(), $frame->properties()->atIndex(0)->type()->__toString());
            $this->assertEquals('Foo', $frame->properties()->atIndex(1)->classType());
            $this->assertEquals('Bar', $frame->properties()->atIndex(1)->type()->__toString());
        }
        ];
    }
}
