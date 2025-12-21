<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ImplementsTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'tokensAndTypes',
    ];

    /**
     * @param array<array-key, Token|TypeNode> $tokensAndTypes
     */
    public function __construct(
        public Token $tag,
        public array $tokensAndTypes = []
    ) {
    }

    /**
     * @return TypeNode[]
     */
    public function types(): array
    {
        return array_filter($this->tokensAndTypes, function ($node) {
            return $node instanceof TypeNode;
        });
    }
}
