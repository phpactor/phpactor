<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class NullNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'null',
    ];

    public Token $null;

    public function __construct(Token $null)
    {
        $this->null = $null;
    }

    public function null(): Token
    {
        return $this->null;
    }
}
