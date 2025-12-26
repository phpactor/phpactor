<?php

namespace Phpactor\DocblockParser\Ast\Value;

use Phpactor\DocblockParser\Ast\ValueNode;
use Phpactor\DocblockParser\Ast\Token;

class NullValue extends ValueNode
{
    public function __construct(private readonly Token $null)
    {
    }

    public function null(): Token
    {
        return $this->null;
    }

    public function value()
    {
        return null;
    }
}
