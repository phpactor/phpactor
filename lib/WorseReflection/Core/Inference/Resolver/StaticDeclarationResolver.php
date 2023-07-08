<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\StaticVariableDeclaration;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class StaticDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof StaticVariableDeclaration);
        $name = NodeUtil::nameFromTokenOrNode($node, $node->variableName);

        if (!$name) {
            return NodeContextFactory::forNode($node);
        }

        $context = NodeContextFactory::create(
            $name,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => TypeFactory::mixed(),
            ]
        );
        $frame->locals()->set(PhpactorVariable::fromSymbolContext($context)->asAssignment());

        return NodeContextFactory::forNode($node);
    }
}
