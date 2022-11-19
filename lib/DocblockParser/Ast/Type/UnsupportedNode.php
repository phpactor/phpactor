<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class UnsupportedNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'token',
    ];

    public function __construct(public Token $token)
    {
    }
}
