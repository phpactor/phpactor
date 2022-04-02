<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Types;

class QualifiedNameListResolver implements Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
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
            $types[] = $resolver->resolveNode($frame, $child)->type();
        }

        $types = Types::fromTypes($types);
        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => $types->best(),
                'types' => $types,
                'symbol_type' => Symbol::CLASS_,
            ]
        );
    }
}
