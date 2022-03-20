<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TextNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ReturnTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
        'text',
    ];
    
    public ?TypeNode $type;
    
    public ?TextNode $text;
    
    public Token $tag;

    public function __construct(Token $tag, ?TypeNode $type, ?TextNode $text = null)
    {
        $this->type = $type;
        $this->text = $text;
        $this->tag = $tag;
    }

    public function type(): ?TypeNode
    {
        return $this->type;
    }

    public function text(): ?TextNode
    {
        return $this->text;
    }
}
