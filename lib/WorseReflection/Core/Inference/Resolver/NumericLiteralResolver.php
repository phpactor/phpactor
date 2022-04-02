<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NumericLiteral;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class NumericLiteralResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof NumericLiteral);

        // Strip PHP 7.4 underscorse separator before comparison
        $value = $this->convertNumericStringToInternalType(
            str_replace('_', '', $node->getText())
        );

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::NUMBER,
                'type' => is_float($value) ? TypeFactory::float() : TypeFactory::int(),
                'value' => $value,
                'container_type' => NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node),
            ]
        );
    }

    /**
     * @return int|float
     */
    private function convertNumericStringToInternalType(string $value)
    {
        if (1 === preg_match('/^[1-9][0-9]*$/', $value)) {
            return (int) $value;
        }
        if (1 === preg_match('/^0[xX][0-9a-fA-F]+$/', $value)) {
            return hexdec(substr($value, 2));
        }
        if (1 === preg_match('/^0[0-7]+$/', $value)) {
            return octdec(substr($value, 1));
        }
        if (1 === preg_match('/^0[bB][01]+$/', $value)) {
            return bindec(substr($value, 2));
        }

        return (float) $value;
    }
}
