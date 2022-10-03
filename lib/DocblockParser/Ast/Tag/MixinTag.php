<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\Type\ClassNode;

class MixinTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'class'
    ];

    public ?ClassNode $class;

    public Token $tag;

    public function __construct(Token $tag, ?ClassNode $class)
    {
        $this->class = $class;
        $this->tag = $tag;
    }

    public function class(): ?ClassNode
    {
        return $this->class;
    }
}
