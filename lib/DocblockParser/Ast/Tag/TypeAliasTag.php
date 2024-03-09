<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Token;

class TypeAliasTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'alias',
        'type',
    ];

    public function __construct(public Token $tag, public ?TypeNode $alias, public ?TypeNode $type)
    {
    }
}
