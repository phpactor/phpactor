<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;

use Generator;

class Docblock extends Node
{
    protected const CHILD_NAMES = [
        'children'
    ];

    /**
     * @var ElementList<Element>
     */
    public ElementList $children;

    /**
     * @param Element[] $children
     */
    public function __construct(array $children)
    {
        $this->children = new ElementList($children);
    }

    /**
     * @param class-string $tagFqn
     */
    public function hasTag(string $tagFqn): bool
    {
        foreach ($this->tags() as $tag) {
            if ($tag instanceof $tagFqn) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of TagNode
     * @param class-string<T>|null $tagFqn
     * @return Generator<T|TagNode>
     */
    public function tags(?string $tagFqn = null): Generator
    {
        foreach ($this->children as $child) {
            if ($tagFqn && $child instanceof $tagFqn) {
                yield $child;
                continue;
            }
            if (!$tagFqn && $child instanceof TagNode) {
                yield $child;
                continue;
            }
        }
    }

    public function prose(): string
    {
        return trim(implode('', array_map(function (Element $token): string {
            if ($token instanceof Token) {
                if (in_array($token->type, [
                    Token::T_PHPDOC_OPEN,
                    Token::T_PHPDOC_CLOSE,
                    Token::T_PHPDOC_LEADING
                ])) {
                    return '';
                }
                return $token->value;
            }
            return '';
        }, iterator_to_array($this->children, false))));
    }
}
