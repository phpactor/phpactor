<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class ReturnTypeWalkerTest extends FrameWalkerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public static function provideWalk(): Generator
    {
        yield 'Get return type from frame' => [
            <<<'EOT'
                <?php

                return 'string';
                <>
                EOT
        ,
            function (Frame $frame): void {
                self::assertEquals('"string"', $frame->returnType()->__toString());
            }
                ];

        yield 'Get union return type from frame' => [
            <<<'EOT'
                <?php

                function foo() {
                    if ($baz) {
                        return null;
                    }
                    return 'string';
                    <>
                }

                <>
                EOT
        ,
            function (Frame $frame): void {
                self::assertEquals('null|"string"', $frame->returnType()->__toString());
            }
        ];

        yield 'Get do not duplicate union return type from frame' => [
            <<<'EOT'
                <?php

                function foo() {
                    if ($baz) {
                        return null;
                    }
                    if ($boo) {
                        return 'string';
                    }
                    return 'string';
                    <>
                }

                <>
                EOT
        ,
            function (Frame $frame): void {
                self::assertEquals('null|"string"', $frame->returnType()->__toString());
            }
        ];
    }
}
