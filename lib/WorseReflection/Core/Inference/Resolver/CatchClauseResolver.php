<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Microsoft\PhpParser\Node\CatchClause;
use Phpactor\WorseReflection\Core\Inference\Symbol;

class CatchClauseResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        $context = NodeContextFactory::create('catch', $node->getStartPosition(), $node->getEndPosition());
        assert($node instanceof CatchClause);

        /** @phpstan-ignore-next-line Lies */
        if (!$node->qualifiedNameList instanceof QualifiedNameList) {
            return $context;
        }

        /** @phpstan-ignore-next-line Lies */
        $type = $resolver->resolveNode($frame, $node->qualifiedNameList)->type();
        $variableName = $node->variableName;

        if (null === $variableName) {
            return $context;
        }

        $context = NodeContextFactory::create(
            (string)$variableName->getText($node->getFileContents()),
            $variableName->getStartPosition(),
            $variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $type,
            ]
        );

        $frame->locals()->set(Variable::fromSymbolContext($context));

        return $context;
    }
}
