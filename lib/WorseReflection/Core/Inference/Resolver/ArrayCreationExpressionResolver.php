<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;

class ArrayCreationExpressionResolver implements Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ArrayCreationExpression);

        $array  = [];

        if (null === $node->arrayElements) {
            return NodeContextFactory::create(
                $node->getText(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'type' => TypeFactory::array(),
                    'value' => []
                ]
            );
        }

        foreach ($node->arrayElements->getElements() as $element) {
            $value = $resolver->resolveNode($frame, $element->elementValue)->value();
            if ($element->elementKey) {
                $key = $resolver->resolveNode($frame, $element->elementKey)->value();
                $array[$key] = $value;
                continue;
            }

            $array[] = $value;
        }

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => TypeFactory::array(),
                'value' => $array
            ]
        );
    }

}
