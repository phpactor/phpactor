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
        return $this->doResolve($resolver, $frame, $node, $frame->locals()->toVariables());
    }

    private function doResolve(FrameResolver $resolver, Frame $frame, Node $node, Variables $variables): Variables
    {
        if ($node instanceof ArgumentExpression) {
            return $this->doResolve($resolver, $frame, $node->expression, $variables);
        }

        if ($node instanceof BinaryExpression) {
            return $this->resolveBinaryExpression($resolver, $frame, $node, $variables);
        }

        if ($node instanceof UnaryExpression) {
            return $this->resolveUnaryOperator($resolver, $frame, $node, $variables);
        }

        return $variables;
    }

    private function resolveBinaryExpression(FrameResolver $resolver, Frame $frame, BinaryExpression $node, Variables $variables): Variables
    {
        if ($node->operator->kind == TokenKind::InstanceOfKeyword) {
            $left = $resolver->resolveNode($frame, $node->leftOperand);
            $right = $resolver->resolveNode($frame, $node->rightOperand)->type();
            return new Variables([Variable::fromSymbolContext($left)->withType($right)]);
        }

        if ($node->operator->kind == TokenKind::AmpersandAmpersandToken) {
            $leftVars = $this->doResolve($resolver, $frame, $node->leftOperand, $variables);
            $rightVars = $this->doResolve($resolver, $frame, $node->rightOperand, $variables);
            return $leftVars->and($rightVars);
        }

        return $variables;
    }

    private function resolveUnaryOperator(FrameResolver $resolver, Frame $frame, UnaryExpression $node, Variables $variables): Variables
    {
        $operatorKind = NodeUtil::operatorKindForUnaryExpression($node);
        $return = new Variables([]);

        if ($operatorKind === TokenKind::ExclamationToken) {
            $opVariables = $this->doResolve($resolver, $frame, $node->operand, $variables);

            foreach ($opVariables as $opVariable) {
                $originalVar = $variables->getOrCreate($opVariable->name());
                $return->add($originalVar->withType(
                    TypeFactory::exclude(
                        $originalVar->type(),
                        $opVariable->type()
                    )
                ));
            }
        }
      
        return $return;
    }
}
