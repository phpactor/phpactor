<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
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

        /** @phpstan-ignore-next-line Lies */
        if (!$node->qualifiedNameList instanceof QualifiedNameList) {
            return $frame;
        }

        /** @phpstan-ignore-next-line Lies */
        $type = $resolver->resolveNode($frame, $node->qualifiedNameList)->type();
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
                'type' => $type,
            ]
        );

        $frame->locals()->add(Variable::fromSymbolContext($context));

        return $frame;
    }
}
