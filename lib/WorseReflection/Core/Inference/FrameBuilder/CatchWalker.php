<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Microsoft\PhpParser\Node\CatchClause;
use Phpactor\WorseReflection\Core\Inference\Symbol;

class CatchWalker extends AbstractWalker
{
    public function nodeFqns(): array
    {
        return [CatchClause::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CatchClause);
        if (!$node->qualifiedNameList) {
            return $frame;
        }

        $types = $resolver->resolveNode($frame, $node->qualifiedNameList)->types();
        $variableName = $node->variableName;

        if (null === $variableName) {
            return $frame;
        }

        $context = NodeContextFactory::create(
            (string)$variableName->getText($node->getFileContents()),
            $variableName->getStartPosition(),
            $variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'types' => $types,
            ]
        );

        $frame->locals()->add(Variable::fromSymbolContext($context));

        return $frame;
    }
}
