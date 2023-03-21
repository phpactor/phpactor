<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\TypeUtil;

class ArrayCreationExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ArrayCreationExpression);

        $array  = [];

        if (null === $node->arrayElements) {
            return NodeContextFactory::create(
                $node->getText(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'type' => TypeFactory::arrayLiteral([]),
                ]
            );
        }

        foreach ($node->arrayElements->getElements() as $element) {
            $value = $resolver->resolveNode($frame, $element->elementValue)->type();
            if ($element->elementKey) {
                $key = $resolver->resolveNode($frame, $element->elementKey)->type();
                $keyValue = TypeUtil::valueOrNull($key);
                if (null === $keyValue) {
                    continue;
                }
                $array[$keyValue] = $value;
                continue;
            }

            $array[] = $value;
        }

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => TypeFactory::arrayLiteral($array),
            ]
        );
    }
}
