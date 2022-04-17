<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class LiteralFloatNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'token',
    ];

    public Token $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }
}
