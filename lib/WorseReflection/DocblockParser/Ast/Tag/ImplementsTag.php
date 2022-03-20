<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;

class ImplementsTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
    ];
    
    public Token $tag;
    
    public TypeNode $type;

    public function __construct(Token $tag, ?TypeNode $type = null)
    {
        $this->tag = $tag;
        $this->type = $type;
    }
}
