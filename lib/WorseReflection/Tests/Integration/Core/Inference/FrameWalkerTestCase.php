<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Closure;
use Generator;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

abstract class FrameWalkerTestCase extends IntegrationTestCase
{
    /**
     * @dataProvider provideWalk
     */
    public function testWalk(string $source, Closure $assertion): void
    {
        list($source, $offset) = ExtractOffset::fromSource($source);
        $path = $this->workspace()->path('test.php');
        $source = SourceCode::fromPathAndString($path, $source);
        $reflector = $this->createReflectorWithWalker($source, $this->walker());
        $reflectionOffset = $reflector->reflectOffset($source, $offset);
        $assertion($reflectionOffset->frame(), $offset);
    }

    abstract public function provideWalk(): Generator;

    public function walker(): ?Framewalker
    {
        return null;
    }

    private function createReflectorWithWalker($source, Walker $frameWalker = null): Reflector
    {
        $reflector = ReflectorBuilder::create();
        $reflector->addSource($source);

        if ($frameWalker) {
            $reflector->addFrameWalker($frameWalker);
        }

        return $reflector->build();
    }
}
