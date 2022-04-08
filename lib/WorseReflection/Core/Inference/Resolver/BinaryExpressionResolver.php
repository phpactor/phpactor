<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\Comparable;
use Phpactor\WorseReflection\Core\Type\Concatable;
use Phpactor\WorseReflection\Core\Type\MissingType;

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

        return new MissingType();
        /**
        switch (strtolower($operator)) {
            case 'or':
                return $left or $right;
            case '||':
                return $left or $right;
            case 'and':
                return $left and $right;
            case '&&':
                return $left && $right;
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '%':
                // do not cause fatal error if right value is zero-ish
                if (!$right) {
                    return 0;
                }
                return $left % $right;
            case '/':
                // do not cause fatal error if right value is zero-ish
                if (!$right) {
                    return 0;
                }

                return $left / $right;
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
            case 'xor':
                return $left xor $right;
        }
        **/
    }
}
