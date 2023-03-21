<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class FunctionDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof FunctionDeclaration);

        $resolver->resolveNode($context, $node->compoundStatementOrSemicolon);

        // TODO: position was the *name* position, need to update this to use a dedicated context
        return $context
            ->withSymbolName(
                (string)$node->name?->getText((string)$node->getFileContents()),
            )
            ->withSymbolType(Symbol::FUNCTION);
    }
}
