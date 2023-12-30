<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\NodeContextFromMemberAccess;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;

class ScopedPropertyAccessResolver implements Resolver
{
    public function __construct(
        private NodeContextFromMemberAccess $nodeContextFromMemberAccess
    ) {
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
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

        $classType = $resolver->resolveNode($frame, $node->scopeResolutionQualifier)->type();

        if ($classType instanceof ClassStringType && $classType->className()) {
            $classType = TypeFactory::reflectedClass($resolver->reflector(), $classType->className());
        }

        return $this->nodeContextFromMemberAccess->infoFromMemberAccess($resolver, $frame, $classType, $node);
    }
}
