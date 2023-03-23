<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class NamespaceUseDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof NamespaceUseDeclaration);
        foreach ($node->useClauses->getElements() as $useStatement) {
            $resolver->resolveNode($context, $useStatement);
        }
        return $context;
    }
}
