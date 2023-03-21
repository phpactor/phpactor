<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\UnionType;

class QualifiedNameListResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof QualifiedNameList);
        $types = [];
        $firstType = null;
        foreach ($node->getChildNodes() as $child) {
            if (!$child instanceof QualifiedName) {
                continue;
            }
            if (null === $firstType) {
                $firstType = $child;
            }
            $types[] = $resolver->resolveNode($context, $child)->type();
        }

        $type = new UnionType(...$types);
        return $context->withSymbolType(Symbol::CLASS_)->withType($type->reduce());
    }
}
