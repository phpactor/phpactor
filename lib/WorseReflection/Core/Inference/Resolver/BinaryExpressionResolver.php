<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\ExpressionEvaluator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\Comparable;
use Phpactor\WorseReflection\Core\Type\Concatable;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\TypeUtil;

class BinaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof BinaryExpression);

        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
            ]
        );

        $left = $resolver->resolveNode($frame, $node->leftOperand)->type();
        $right = $resolver->resolveNode($frame, $node->rightOperand)->type();
        $operator = $node->operator->getText($node->getFileContents());

        if (!is_string($operator)) {
            return $context;
        }

        return $context->withType($this->walkBinaryExpression($resolver, $frame, $left, $right, $operator, $node));
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

        if ($left instanceof ClassType) {
            switch ($operator) {
                case 'instanceof':
                    return $left->instanceof($right);
            }
        }

        return new MissingType();
        /**
        switch (strtolower($operator)) {
            case 'instanceof':
                return true;
            case '&':
                return $left & $right;
            case '|':
                return $left | $right;
            case '^':
                return $left ^ $right;
            case '<<':
                return $left << $right;
            case '>>':
                return $left >> $right;
        }
        **/
    }
}
