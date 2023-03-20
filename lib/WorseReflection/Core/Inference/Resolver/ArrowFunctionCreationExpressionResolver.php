<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArrowFunctionCreationExpression;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ArrowFunctionCreationExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        assert($node instanceof ArrowFunctionCreationExpression);
        $returnType = NodeUtil::typeFromQualfiedNameLike(
            $resolver->reflector(),
            $node,
            $node->returnTypeList
        );

        $args = [];
        /** @phpstan-ignore-next-line [TR] No trust */
        if ($node->parameters) {
            foreach ($node->parameters->getChildNodes() as $parameter) {
                if (!$parameter instanceof Parameter) {
                    continue;
                }
                $args[] = $resolver->resolveNode($frame, $parameter)->type();
            }
        }

        if (!$returnType->isDefined()) {
            $returnType = $resolver->resolveNode($frame, $node->resultExpression)->type()->generalize();
        }

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => new ClosureType($resolver->reflector(), $args, $returnType),
            ]
        );
    }
}
