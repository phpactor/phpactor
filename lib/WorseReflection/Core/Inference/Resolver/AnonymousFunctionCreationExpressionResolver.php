<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class AnonymousFunctionCreationExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof AnonymousFunctionCreationExpression);
        $type = NodeUtil::typeFromQualfiedNameLike(
            $resolver->reflector(),
            $node,
            $node->returnTypeList
        );

        $args = [];
        /** @phpstan-ignore-next-line [TR] No trust */
        if ($node->parameters) {
            foreach ($node->parameters->getChildNodes() as $parameter) {
                if (!$parameter instanceof Parameter) {
                    continue;
                }
                $args[] = $resolver->resolveNode($context, $parameter)->type();
            }
        }

        $type = new ClosureType($resolver->reflector(), $args, $type);

        return $context->withType($type);
    }
}
