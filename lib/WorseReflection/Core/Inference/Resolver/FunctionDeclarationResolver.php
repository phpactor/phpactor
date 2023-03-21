<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class FunctionDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof FunctionDeclaration);

        $resolver->resolveNode($frame, $node->compoundStatementOrSemicolon);

        return NodeContextFactory::create(
            (string)$node->name?->getText((string)$node->getFileContents()),
            // TODO: Q: Why is this the position of the function name? A: Goto definition, this should be a rich NodeContext instance.
            $node->name?->getStartPosition() ?? 0,
            $node->name?->getEndPosition() ?? 0,
            [
                'symbol_type' => Symbol::FUNCTION,
            ]
        );
    }
}
