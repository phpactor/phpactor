<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\NodeContextFromMemberAccess;

class MemberAccessExpressionResolver implements Resolver
{
    public function __construct(private NodeContextFromMemberAccess $nodeContextFromMemberAccess)
    {
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression);

        $class = $resolver->resolveNode($frame, $node->dereferencableExpression);

        $context = $this->nodeContextFromMemberAccess->infoFromMemberAccess($resolver, $frame, $class->type(), $node);
        return new MemberAccessContext(
            $context->symbol(),
            $context->type(),
            $context->containerType(),
        );
    }
}
