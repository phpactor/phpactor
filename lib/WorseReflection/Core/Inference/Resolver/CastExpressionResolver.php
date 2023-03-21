<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CastExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CastExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof CastExpression);

        $type = NodeUtil::nameFromTokenOrNode($node, $node->castType);
        $type = rtrim(ltrim($type, '('), ')');
        $type = TypeFactory::fromStringWithReflector($type, $resolver->reflector());
        $context = $context->withType($type);

        if (!in_array($type->__toString(), [
            'string',
            'bool',
            'float',
            'string',
            'array',
            'object',
            'integer',
            'boolean',
            'double'
        ])) {
            $context = $context->withIssue(sprintf('Unsupported cast "%s"', $type->__toString()));
        }

        return $context;
    }
}
