<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextModifier;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BitwiseOperable;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\Comparable;
use Phpactor\WorseReflection\Core\Type\Concatable;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;

class BinaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof BinaryExpression);

        $operator = $node->operator->getText($node->getFileContents());
        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
            ]
        );

        $left = $resolver->resolveNode($frame, $node->leftOperand);
        $right = $resolver->resolveNode($frame, $node->rightOperand);

        if (!is_string($operator)) {
            return $context;
        }

        // can remove given the other logic below??
        switch ($operator) {
            case 'instanceof':
                return $this->resolveInstanceOf($context, $resolver, $frame, $node->leftOperand, $right);
        }

        $context = $context->withTypeAssertions(
            $left->typeAssertions()->merge($right->typeAssertions())
        );
        $type = $this->walkBinaryExpression($resolver, $frame, $left->type(), $right->type(), $operator, $node);

        if ($type instanceof BooleanType) {
            if (false === $type->isTrue()) {
                $context = $context->withTypeAssertions($context->typeAssertions()->negate());
            }
        }

        return $context->withType($type);
    }

    private function walkBinaryExpression(
        NodeContextResolver $resolver,
        Frame $frame,
        Type $left,
        Type $right,
        string $operator,
        BinaryExpression $node
    ): Type {
        if ($left instanceof Concatable) {
            switch ($operator) {
                case '.':
                case '.=':
                    return $left->concat($right);
            }
        }

        if ($left instanceof Comparable) {
            switch ($operator) {
                case '===':
                    return $left->identical($right);
                case '==':
                    return $left->equal($right);
                case '>':
                    return $left->greaterThan($right);
                case '>=':
                    return $left->greaterThanEqual($right);
                case '>':
                    return $left->greaterThan($right);
                case '<':
                    return $left->lessThan($right);
                case '<=':
                    return $left->lessThanEqual($right);
                case '!=':
                    return $left->notEqual($right);
                case '!==':
                    return $left->notIdentical($right);
            }
        }

        switch ($operator) {
            case 'or':
            case '||':
                return TypeUtil::toBool($left)->or(TypeUtil::toBool($right));
            case 'and':
            case '&&':
                return TypeUtil::toBool($left)->and(TypeUtil::toBool($right));
            case 'xor':
                return TypeUtil::toBool($left)->xor(TypeUtil::toBool($right));
            case '+':
                return TypeUtil::toNumber($left)->plus(TypeUtil::toNumber($right));
            case '-':
                return TypeUtil::toNumber($left)->minus(TypeUtil::toNumber($right));
            case '*':
                return TypeUtil::toNumber($left)->multiply(TypeUtil::toNumber($right));
            case '/':
                return TypeUtil::toNumber($left)->divide(TypeUtil::toNumber($right));
            case '%':
                return TypeUtil::toNumber($left)->modulo(TypeUtil::toNumber($right));
            case '**':
                return TypeUtil::toNumber($left)->exp(TypeUtil::toNumber($right));
        }

        if ($left instanceof BitwiseOperable) {
            switch ($operator) {
                case '&':
                    return $left->bitwiseAnd($right);
                case '|':
                    return $left->bitwiseOr($right);
                case '^':
                    return $left->bitwiseXor($right);
                case '<<':
                    return $left->shiftLeft($right);
                case '>>':
                    return $left->shiftRight($right);
            }
        }

        return new MissingType();
    }

    private function resolveInstanceOf(
        NodeContext $context,
        NodeContextResolver $resolver,
        Frame $frame,
        Node $leftOperand,
        NodeContext $right
    ): NodeContext
    {
        // work around for https://github.com/Microsoft/tolerant-php-parser/issues/19#issue-201714377
        // the left hand side of instanceof should be parsed as a variable but it's not.
        if ($leftOperand instanceof UnaryExpression) {
            $left = $resolver->resolveNode($frame, $leftOperand->operand);
        } else {
            $left = $resolver->resolveNode($frame, $leftOperand);
        }

        $context = $context->withTypeAssertionForSubject($left, $right->type());
        return $context->withType(TypeFactory::boolLiteral(true));
    }
}
