<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NumericLiteral;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class NumericLiteralResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof NumericLiteral);

        $type = TypeFactory::fromNumericString($node->getText());
        assert($type instanceof Literal);
        return $context
            ->withSymbolName($node->getText())
            ->withSymbolType(Symbol::NUMBER)
            ->withType($type)
            ->withContainerType(
                NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node)
            );
    }

}
