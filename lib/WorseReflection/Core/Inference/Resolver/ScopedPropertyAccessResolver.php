<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Type\ClassType;

class ScopedPropertyAccessResolver implements Resolver
{
    private FullyQualifiedNameResolver $nodeTypeConverter;

    public function __construct(FullyQualifiedNameResolver $nodeTypeConverter)
    {
        $this->nodeTypeConverter = $nodeTypeConverter;
    }

    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ScopedPropertyAccessExpression);

        $name = null;
        if ($node->scopeResolutionQualifier instanceof Variable) {
            $context = $resolver->resolveNode(
                $frame,
                $node->scopeResolutionQualifier
            );
            $type = $context->type();
            if ($type instanceof ClassType) {
                $name = $type->name->__toString();
            }
        }

        if (empty($name)) {
            $name = $node->scopeResolutionQualifier->getText();
        }

        $parent = $this->nodeTypeConverter->resolve($node, $name);

        return MemberAccessExpressionResolver::infoFromMemberAccess($resolver, $frame, $parent, $node);
    }
}
