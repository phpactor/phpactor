<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class IncludeWalkerTest extends FrameWalkerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->workspace()->reset();
        $this->workspace()->put('foo.php', '<?php $foo = "bar";');
        $this->workspace()->put('return_value.php', '<?php return "bar";');
    }

    public function provideWalk(): Generator
    {
        // disabled this walker for now due to perforamnce and behavioral
        // issues.
        return;
        yield 'Require relative' => [
            <<<'EOT'
                <?php

                require('foo.php');
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->first()->name());
            }
        ];

        yield 'Require absolute' => [
            <<<EOT
                <?php

                require('{$this->workspace()->path('foo.php')}');
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->first()->name());
            }
        ];

        yield 'Include' => [
            <<<'EOT'
                <?php

                include('foo.php');
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->first()->name());
            }
        ];

        yield 'Returns value' => [
            <<<'EOT'
                <?php

                $foo = require('return_value.php');
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->last()->name());
                $this->assertEquals('"bar"', (string) $frame->locals()->last()->type());
            }
        ];

        yield 'Returns from constant' => [
            <<<'EOT'
                <?php

                $foo = require(__DIR__ . '/return_value.php');
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->last()->name());
                $this->assertEquals('"bar"', (string) $frame->locals()->last()->type());
            }
        ];
    }
}
