<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Microsoft\PhpParser\Node\CatchClause;
use Phpactor\WorseReflection\Core\Inference\Symbol;

class CatchWalker extends AbstractWalker
{
    public function canWalk(Node $node): bool
    {
        return $node instanceof CatchClause;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CatchClause);
        if (!$node->qualifiedNameList) {
            return $frame;
        }

        $types = $builder->resolveNode($frame, $node->qualifiedNameList)->types();
        $variableName = $node->variableName;

        if (null === $variableName) {
            return $frame;
        }

        $context = $this->symbolFactory()->context(
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
