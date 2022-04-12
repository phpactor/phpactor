<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class StringLiteralResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof StringLiteral);
        // TODO: [TP] tolerant parser method returns the quotes
        $value = (string) $this->getStringContentsText($node);
        return NodeContextFactory::create(
            'string',
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::STRING,
                'type' => TypeFactory::stringLiteral($value),
                'container_type' => NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node),
            ]
        );
    }

    private function getStringContentsText(StringLiteral $node): string
    {
        $children = $node->children;
        if ($children instanceof Token) {
            $value = (string)$children->getText($node->getFileContents());
            $startQuote = substr($node, 0, 1);

            if ($startQuote === '\'') {
                return rtrim(substr($value, 1), '\'');
            }

            if ($startQuote === '"') {
                return rtrim(substr($value, 1), '"');
            }
        }

        return '';
    }
}
