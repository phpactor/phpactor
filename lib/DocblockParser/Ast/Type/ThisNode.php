<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ThisNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'name'
    ];

    public function __construct(public Token $name)
    {
    }
}
