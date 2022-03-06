<?php

namespace Phpactor\Extension\WorseReflection\Tests\Unit;

use Microsoft\PhpParser\Node;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Position;

class TestExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register('test.framewalker', function (Container $container) {
            return new TestFrameWalker();
        }, [ WorseReflectionExtension::TAG_FRAME_WALKER => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}

class TestFrameWalker implements FrameWalker
{
    public function canWalk(Node $node): bool
    {
        return true;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        if ($frame->locals()->byName('test_variable')->count()) {
            return $frame;
        }

        $frame->locals()->add(
            Variable::fromSymbolContext(
                SymbolContext::for(Symbol::fromTypeNameAndPosition('variable', 'test_variable', Position::fromFullStartStartAndEnd(0, 1, 10)))
            )
        );
        return $frame;
    }
}
