<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ReservedWord;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReservedWordResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        assert($node instanceof ReservedWord);
        $symbolType = $containerType = $type = $value = null;
        $word = strtolower($node->getText());

        if ('null' === $word) {
            $type = TypeFactory::null();
            $symbolType = Symbol::UNKNOWN;
            $containerType = NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node);
        }

        if ('false' === $word) {
            $value = false;
            $type = TypeFactory::boolLiteral($value);
            $symbolType = Symbol::BOOLEAN;
            $containerType = NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node);
        }

        if ('true' === $word) {
            $value = true;
            $type = TypeFactory::boolLiteral($value);
            $symbolType = Symbol::BOOLEAN;
            $containerType = NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node);
        }

        $info = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => $type,
                'symbol_type' => $symbolType === null ? Symbol::UNKNOWN : $symbolType,
                'container_type' => $containerType,
            ]
        );

        if (null === $symbolType) {
            $info = $info->withIssue(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        if (null === $type) {
            $info = $info->withIssue(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        return $info;
    }
}
