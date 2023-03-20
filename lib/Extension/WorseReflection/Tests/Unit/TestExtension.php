<?php

namespace Phpactor\Extension\WorseReflection\Tests\Unit;

use Microsoft\PhpParser\Node;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\TextDocument\ByteOffsetRange;

class TestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('test.framewalker', function (Container $container) {
            return new TestFrameWalker();
        }, [ WorseReflectionExtension::TAG_FRAME_WALKER => []]);
    }


    public function configure(Resolver $schema): void
    {
    }
}

class TestFrameWalker implements Walker
{
    public function enter(FrameResolver $builder, Frame $frame, Node $node): Frame
    {
        if ($frame->locals()->byName('test_variable')->count()) {
            return $frame;
        }

        $frame->locals()->set(
            Variable::fromSymbolContext(
                NodeContext::for(Symbol::fromTypeNameAndPosition('variable', 'test_variable', ByteOffsetRange::fromFullStartStartAndEnd(0, 1, 10)))
            )
        );
        return $frame;
    }

    public function exit(FrameResolver $builder, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    public function nodeFqns(): array
    {
        return [];
    }
}
