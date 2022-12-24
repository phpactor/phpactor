<?php

namespace Phpactor\Extension\PHPUnit\Tests\Unit\FrameWalker;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\PHPUnit\FrameWalker\AssertInstanceOfWalker;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;

class AssertInstanceOfWalkerTest extends TestCase
{
    /**
     * @dataProvider provideWalk
     */
    public function testWalk(string $source, Closure $assertion): void
    {
        list($source, $offset) = ExtractOffset::fromSource($source);
        $reflector = $this->createReflector($source);
        $reflectionOffset = $reflector->reflectOffset($source, $offset);
        $assertion($reflectionOffset->frame(), $offset);
    }

    /**
     * @return Generator<string,array{string,Closure(Frame): void}>
     */
    public function provideWalk(): Generator
    {
        yield 'no op' => [
            <<<'EOT'
<?php

<>
EOT
            , function (Frame $frame) {
                $this->assertCount(0, $frame->locals());
            }
        ];

        yield 'static assert call with class constant' => [
            <<<'EOT'
<?php

use PHPUnit\Framework\Assert;

$foo;
Assert::assertInstanceOf(stdClass::class, $foo);
<>
EOT
            , function (Frame $frame) {
                $variable = $frame->locals()->byName('foo')->last();
                $this->assertEquals('stdClass', $variable->type()->__toString());
            }
        ];

        yield 'static assert call with stirng' => [
            <<<'EOT'
<?php

use PHPUnit\Framework\Assert;

$foo;
Assert::assertInstanceOf("stdClass", $foo);
<>
EOT
            , function (Frame $frame) {
                $variable = $frame->locals()->byName('foo')->last();
                $this->assertEquals('stdClass', $variable->type()->__toString());
            }
        ];

        yield 'member call' => [
            <<<'EOT'
<?php

use PHPUnit\Framework\TestCase;

class FoobarTest extends TestCase
{
    public function testFoobar()
    {
        $foo;
        $this->assertInstanceOf('Bar\Foo', $foo);
        <>
    }
}
<>
EOT
            , function (Frame $frame) {
                $variable = $frame->locals()->byName('foo')->last();
                $this->assertEquals('Bar\\Foo', $variable->type()->__toString());
            }
        ];
    }

    private function createReflector($source): Reflector
    {
        return ReflectorBuilder::create()
            ->addSource($source)
            ->addFrameWalker(new AssertInstanceOfWalker())
            ->build();
    }
}
