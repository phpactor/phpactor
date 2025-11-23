<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class AssertWalkerTest extends FrameWalkerTestCase
{
    public static function provideWalk(): Generator
    {
        yield 'assert instanceof' => [
            <<<'EOT'
                <?php

                assert($foobar instanceof Foobar);
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('Foobar', (string) $frame->locals()->first()->type());
            }
        ];

        yield 'assert instanceof negative' => [
            <<<'EOT'
                <?php

                assert(!$foobar instanceof Foobar);
                <>
                EOT
        ,
            function (Frame $frame): void {
                $this->assertEquals(1, $frame->locals()->count());
                $this->assertEquals('<missing>', $frame->locals()->first()->type()->__toString());
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
                        assert($this->bar instanceof Bar);

                        <>
                    }
                }
                EOT
        , function (Frame $frame, int $offset): void {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals('Foo', $frame->locals()->atIndex(0)->type()->__toString());
            $this->assertCount(2, $frame->properties());
            $this->assertEquals('Foo', $frame->properties()->atIndex(1)->classType()->__toString());
            $this->assertEquals('Bar', $frame->properties()->atIndex(1)->type()->__toString());
        }];
    }
}
