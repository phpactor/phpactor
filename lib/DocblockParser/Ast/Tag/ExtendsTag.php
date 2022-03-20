<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ExtendsTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
    ];
    
    public Token $tag;
    
    public ?TypeNode $type;

    public function __construct(Token $tag, ?TypeNode $type = null)
    {
        $this->tag = $tag;
        $this->type = $type;
    }
}
