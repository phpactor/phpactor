<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\DelimitedList\ListExpressionList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;
use Psr\Log\LoggerInterface;

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

        $frame->locals()->add($leftOperand->getStartPosition(), WorseVariable::fromSymbolContext($context));
    }
}
