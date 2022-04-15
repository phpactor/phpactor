<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class BinaryExpressionWalker extends AbstractWalker
{
    public function nodeFqns(): array
    {
        return [BinaryExpression::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if (!$node->parent instanceof ExpressionStatement) {
            return $frame;
        }

        assert($node instanceof BinaryExpression);

        $context = $resolver->resolveNode($frame, $node);

        if ($node->leftOperand instanceof Variable) {
            $this->walkParserVariable($frame, $node->leftOperand, $context);
            return $frame;
        }

        return $frame;
    }

    private function walkParserVariable(Frame $frame, Variable $leftOperand, NodeContext $context): void
    {
        $name = NodeUtil::nameFromTokenOrNode($leftOperand, $leftOperand->name);
        $context = NodeContextFactory::create(
            $name,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $context->type(),
            ]
        );

        $frame->locals()->add(WorseVariable::fromSymbolContext($context));
    }
}
