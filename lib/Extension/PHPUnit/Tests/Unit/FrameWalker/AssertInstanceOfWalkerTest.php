<?php

namespace Phpactor\Extension\PHPUnit\Tests\Unit\FrameWalker;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\PHPUnit\FrameWalker\AssertInstanceOfWalker;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Walker\TestAssertWalker;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;

class AssertInstanceOfWalkerTest extends TestCase
{
    /**
     * @dataProvider provideWalk
     */
    public function estWalk(string $source, Closure $assertion): void
    {
        list($source, $offset) = ExtractOffset::fromSource($source);
        $reflector = $this->createReflector($source);
        $reflectionOffset = $reflector->reflectOffset($source, $offset);
        $assertion($reflectionOffset->frame(), $offset);
    }

    public function testStaticAssertCallWithClassConstant(): void
    {
        $this->resolve(<<<'EOT'
            <?php

            use PHPUnit\Framework\Assert;

            $foo;
            Assert::assertInstanceOf(stdClass::class, $foo);
            wrAssertType('stdClass', $foo);
            EOT);
    }

    public function testStaticAssertCallWithClassString(): void
    {
        $this->resolve(<<<'EOT'
            <?php

            use PHPUnit\Framework\Assert;

            $foo;
            Assert::assertInstanceOf('foo\bar', $foo);
            wrAssertType('foo\bar', $foo);
            EOT);
    }

    public function testInstanceCall(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php

                use PHPUnit\Framework\Assert;

                $foo;
                Assert::assertInstanceOf("stdClass", $foo);
                wrAssertType('stdClass', $foo);
                EOT
        );

    }

    /**
     * @return Generator<string,array{string,Closure(Frame): void}>
     */
    public function provideWalk(): Generator
    {
        yield 'member call' => [
            <<<'EOT'
<>
EOT
        , function (Frame $frame) {
            $variable = $frame->locals()->byName('foo')->last();
            $this->assertEquals('Bar\\Foo', $variable->type()->__toString());
        }
        ];
    }

    public function resolve(string $sourceCode): void
    {
        $reflector = ReflectorBuilder::create()
            ->addFrameWalker(new TestAssertWalker($this))
            ->addFrameWalker(new AssertInstanceOfWalker())
            ->addSource($sourceCode)
            ->build();

        $reflector->reflectOffset($sourceCode, mb_strlen($sourceCode));
    }
}
