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
    
    public Token $tag;
    
    /**
     * @var array<array-key, Token|TypeNode>
     */
    public array $tokensAndTypes;

    /**
     * @param array<array-key, Token|TypeNode> $tokensAndTypes
     */
    public function __construct(Token $tag, array $tokensAndTypes = [])
    {
        $this->tag = $tag;
        $this->tokensAndTypes = $tokensAndTypes;
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
