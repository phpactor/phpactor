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
    }

    public function provideWalk(): Generator
    {
        $this->workspace()->put('foo.php', '<?php $foo = "bar";');
        $this->workspace()->put('return_value.php', '<?php return "bar";');

        yield 'Require relative' => [
            <<<'EOT'
                <?php

                require('foo.php');
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->first()->__toString());
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
                $this->assertEquals('foo', $frame->locals()->first()->__toString());
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
                $this->assertEquals('foo', $frame->locals()->first()->__toString());
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
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->last()->__toString());
                $this->assertEquals('string', (string) $frame->locals()->last()->symbolContext()->type());
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
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->last()->__toString());
                $this->assertEquals('string', (string) $frame->locals()->last()->symbolContext()->type());
            }
        ];
    }
}
