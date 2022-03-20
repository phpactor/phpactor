<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;

class TemplateTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'placeholder',
        'constraint',
        'type'
    ];
    
    public Token $tag;
    
    public ?Token $placeholder;
    
    public ?Token $constraint;
    
    public ?TypeNode $type;

    public function __construct(Token $tag, ?Token $placeholder = null, ?Token $constraint = null, ?TypeNode $type = null)
    {
        $this->tag = $tag;
        $this->placeholder = $placeholder;
        $this->constraint = $constraint;
        $this->type = $type;
    }
}
