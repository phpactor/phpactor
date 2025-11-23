<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Closure;

class FrameBuilderTest extends IntegrationTestCase
{
    #[DataProvider('provideForMethod')]
    public function testForMethod(string $source, array $classAndMethod, Closure $assertion): void
    {
        [$className, $methodName] = $classAndMethod;
        $reflector = $this->createReflector($source);
        $method = $reflector->reflectClassLike(ClassName::fromString($className))->methods()->get($methodName);
        $frame = $method->frame();

        $assertion($frame, $this->logger());
    }

    /**
     * @return Generator<string,array{string,array<int,string>,Closure(Frame,LoggerInterface): void}>
     */
    public function provideForMethod(): Generator
    {
        yield 'Tolerates missing assert arguments' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        assert();
                    }
                }
                EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame, $logger): void {
            $this->assertEquals(0, $frame->problems()->count(), $frame->problems()->__toString());
        }];

        yield 'Tolerates missing tokens' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $reflection = )>classReflector->reflect(TestCase::class);
                    }
                }
                EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame, $logger): void {
            $this->assertEquals(0, $frame->problems()->count());
        }];
    }
}
