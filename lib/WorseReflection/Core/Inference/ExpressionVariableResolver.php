<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

final class ExpressionVariableResolver
{
    /**
     * @return Variables
     */
    public function resolve(FrameResolver $resolver, Frame $frame, Node $node): Variables
    {
        return $this->doResolve($resolver, $frame, $node);
    }

    private function doResolve(FrameResolver $resolver, Frame $frame, Node $node): Variables
    {
        if ($node instanceof ArgumentExpression) {
            return $this->doResolve($resolver, $frame, $node->expression);
        }

        if ($node instanceof BinaryExpression) {
            return $this->resolveBinaryExpression($resolver, $frame, $node);
        }

        if ($node instanceof UnaryExpression) {
            return $this->resolveUnaryOperator($resolver, $frame, $node);
        }

        return new Variables([]);
    }

    private function resolveBinaryExpression(FrameResolver $resolver, Frame $frame, BinaryExpression $node): Variables
    {
        if ($node->operator->kind == TokenKind::InstanceOfKeyword) {
            $left = $resolver->resolveNode($frame, $node->leftOperand);
            $right = $resolver->resolveNode($frame, $node->rightOperand)->type();
            return new Variables([Variable::fromSymbolContext($left)->withType($right)]);
        }

        if ($node->operator->kind == TokenKind::AmpersandAmpersandToken) {
            $leftVars = $this->doResolve($resolver, $frame, $node->leftOperand);
            $rightVars = $this->doResolve($resolver, $frame, $node->rightOperand);

            return $leftVars->and($rightVars);
        }

        return new Variables([]);
    }

    private function resolveUnaryOperator(FrameResolver $resolver, Frame $frame, UnaryExpression $node): Variables
    {
        $return = new Variables([]);
        $operatorKind = NodeUtil::operatorKindForUnaryExpression($node);

        if ($operatorKind === TokenKind::ExclamationToken) {
            $opVariables = $this->doResolve($resolver, $frame, $node->operand);

            foreach ($opVariables as $opVariable) {
                $return->addOrMerge($opVariable->withType(
                    TypeFactory::not(
                        $opVariable->type()
                    )
                ));
            }
            return $return;
        }
      
        return $return;
    }
}
