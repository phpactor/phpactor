<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Token;

class MixinTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'class'
    ];

    public function __construct(public Token $tag, public ?TypeNode $class)
    {
    }

    public function class(): ?TypeNode
    {
        return $this->class;
    }
}
