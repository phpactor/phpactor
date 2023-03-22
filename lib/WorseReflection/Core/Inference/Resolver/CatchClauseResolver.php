<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Microsoft\PhpParser\Node\CatchClause;
use Phpactor\WorseReflection\Core\Inference\Symbol;

class CatchClauseResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof CatchClause);

        /** @phpstan-ignore-next-line Lies */
        if (!$node->qualifiedNameList instanceof QualifiedNameList) {
            return $context;
        }

        /** @phpstan-ignore-next-line Lies */
        $type = $resolver->resolveNode($context, $node->qualifiedNameList)->type();
        $variableName = $node->variableName;

        if (null === $variableName) {
            return $context;
        }

        $context = $context->withSymbolName(
            (string)$variableName->getText($node->getFileContents()),
        )->withSymbolType(Symbol::VARIABLE)->withType($type);

        $context->frame()->locals()->set(Variable::fromSymbolContext($context));

        $resolver->resolveNode($context, $node->compoundStatement);

        return $context;
    }
}
