<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Token;

class ScalarNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'name',
    ];

    public function __construct(public Token $name)
    {
    }

    public function name(): Token
    {
        return $this->name;
    }
}
