<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class StringLiteralResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof StringLiteral);
        // TODO: [TP] tolerant parser method returns the quotes
        $value = (string) $this->getStringContentsText($node);
        return $context->withSymbolType(Symbol::STRING)->withType(TypeFactory::stringLiteral($value))
            ->withContainerType(NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node));
    }

    private function getStringContentsText(StringLiteral $node): string
    {
        $children = $node->children;
        if (is_array($children) && array_key_exists(0, $children)) {
            $children = $children[0];
        }

        if ($children instanceof Token) {
            $value = (string)$children->getText($node->getFileContents());
            $startQuote = substr($node, 0, 1);

            return match ($startQuote) {
                '\'' => rtrim(substr($value, 1), '\''),
                '"' => rtrim(substr($value, 1), '"'),
                '<' => trim($value),
                default => ''
            };
        }

        return '';
    }
}
