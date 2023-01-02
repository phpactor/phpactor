<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ListNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'type',
    ];

    public function __construct(public Token $type)
    {
    }
}
