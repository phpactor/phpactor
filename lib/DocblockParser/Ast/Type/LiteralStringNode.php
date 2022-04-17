<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class LiteralStringNode extends TypeNode
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
