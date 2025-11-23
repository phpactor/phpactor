<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Generator;
use Phpactor\WorseReflection\Core\Inference\Frame;

class VariableWalkerTest extends FrameWalkerTestCase
{
    public static function provideWalk(): Generator
    {
        yield 'Redeclared variables' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $foobar = new Foobar();
                        $foobar = new \stdClass();
                        <>
                    }
                }
                EOT
        , function (Frame $frame): void {
            $vars = $frame->locals()->byName('$foobar');
            $this->assertCount(2, $vars);
            $this->assertEquals('Foobar', (string) $vars->first()->type());
            $this->assertEquals('stdClass', (string) $vars->last()->type());
        }];

        yield 'Injects variables with @var (standard)' => [
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

        yield 'Injects variables with @var namespaced' => [
            <<<'EOT'
                <?php
                namespace Foo;
                /** @var Bar $zed */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Foo\\Bar', (string) $frame->locals()->byName('$zed')->last()->type());
            }
        ];

        yield 'Injects variables with @var namespaced and qualified name' => [
            <<<'EOT'
                <?php
                namespace Foo;
                /** @var Bar\Baz $zed */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Foo\\Bar\\Baz', (string) $frame->locals()->byName('$zed')->last()->type());
            }
        ];

        yield 'Injects variables with @var namespaced with fully qualified name' => [
            <<<'EOT'
                <?php
                namespace Foo;
                /** @var \Bar\Baz $zed */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Bar\\Baz', (string) $frame->locals()->byName('$zed')->last()->type());
            }
        ];

        yield 'Injects variables with @var with imported namespace' => [
            <<<'EOT'
                <?php

                use Foo\Bar\Zed;
                /** @var Zed\Baz $zed */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Foo\Bar\Zed\Baz', (string) $frame->locals()->byName('$zed')->last()->type());
            }
        ];

        yield 'Injects named union type' => [
            <<<'EOT'
                <?php

                /** @var Bar|Baz $zed */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Bar|Baz', $frame->locals()->byName('$zed')->last()->type()->__toString());
            }
        ];

        yield 'Unspecified type for following variable' => [
            <<<'EOT'
                <?php

                /** @var \Zed\Baz */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Zed\Baz', (string) $frame->locals()->byName('$zed')->first()->type());
            }
        ];

        yield 'Unspecified type for following variable with class import' => [
            <<<'EOT'
                <?php

                use Zed\Baz;

                /** @var Baz */
                $zed;
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Zed\Baz', (string) $frame->locals()->byName('$zed')->first()->type());
            }
        ];

        yield 'Targeted variable not matching following variable assignment' => [
            <<<'EOT'
                <?php

                class Baz { public function hello(): string {}}
                $foo = new Baz();

                /** @var Baz $foo */
                $zed = $foo->hello();
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
