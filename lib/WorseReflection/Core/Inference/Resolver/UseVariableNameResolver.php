<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;

class UseVariableNameResolver implements Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof UseVariableName);
        $name = $node->getName();

        $varName = ltrim($name, '$');
        $offset = $node->getEndPosition();
        $variables = $frame->locals()->lessThanOrEqualTo($offset)->byName($varName);

        if (0 === $variables->count()) {
            return NodeContextFactory::create(
                $name,
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE
                ]
            )->withIssue(sprintf('Variable "%s" is undefined', $varName));
        }

        return $variables->last()->symbolContext();
    }
}
