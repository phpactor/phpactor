<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\NodeContextFromMemberAccess;
use Phpactor\WorseReflection\Core\Type\ClassType;

class ScopedPropertyAccessResolver implements Resolver
{
    public function __construct(
        private NodeToTypeConverter $nodeTypeConverter,
        private NodeContextFromMemberAccess $nodeContextFromMemberAccess
    ) {
    }

    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof ScopedPropertyAccessExpression);

        $name = null;
        if ($node->scopeResolutionQualifier instanceof Variable) {
            $scopeResContext = $resolver->resolveNode(
                $context,
                $node->scopeResolutionQualifier
            );
            $type = $scopeResContext->type();
            if ($type instanceof ClassType) {
                $name = $type->name->__toString();
            }
        }

        if (empty($name)) {
            $name = $node->scopeResolutionQualifier->getText();
        }

        $classType = $this->nodeTypeConverter->resolve($node, (string)$name);

        return $this->nodeContextFromMemberAccess->infoFromMemberAccess($resolver, $context, $classType, $node);
    }
}
