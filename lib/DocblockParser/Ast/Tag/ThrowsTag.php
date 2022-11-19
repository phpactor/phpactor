<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ThrowsTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'exceptionClass'
    ];

    public function __construct(public Token $tag, public ?TypeNode $exceptionClass)
    {
    }
}
