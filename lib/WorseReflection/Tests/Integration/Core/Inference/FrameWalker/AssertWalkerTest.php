<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class AssertWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
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
                $this->assertEquals('Foobar', (string) $frame->locals()->first()->types()->best());
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
                self::assertEquals(1, $frame->locals()->count());
                self::assertEquals('<missing>', $frame->locals()->byName('foobar')->first()->type());
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
            $this->assertEquals('Foo', $frame->locals()->atIndex(0)->types()->best()->__toString());
            $this->assertCount(1, $frame->properties());
            $this->assertEquals('Foo', $frame->properties()->atIndex(0)->classType()->__toString());
            $this->assertEquals('Bar', $frame->properties()->atIndex(0)->type()->__toString());
        }];
    }
}
