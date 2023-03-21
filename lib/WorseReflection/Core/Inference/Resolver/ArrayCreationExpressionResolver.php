<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\TypeUtil;

class ArrayCreationExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $parentContext, Node $node): NodeContext
    {
        assert($node instanceof ArrayCreationExpression);

        $array  = [];

        $context = $parentContext->addChildFromNode($node);

        if (null === $node->arrayElements) {
            return $context->withType(TypeFactory::arrayLiteral([]));
        }

        /**
         * @var ArrayElement $element
         */
        foreach ($node->arrayElements->getElements() as $element) {
            $value = $resolver->resolveNode($context, $element->elementValue)->type();
            if ($element->elementKey) {
                $key = $resolver->resolveNode($context, $element->elementKey)->type();
                $keyValue = TypeUtil::valueOrNull($key);
                if (null === $keyValue) {
                    continue;
                }
                $array[$keyValue] = $value;
                continue;
            }

            $array[] = $value;
        }

        return $context->withType(TypeFactory::arrayLiteral($array));
    }
}
