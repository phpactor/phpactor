<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ConstantNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'name',
        'doubleColon',
        'constant'
    ];

    public TypeNode $name;

    public Token $doubleColon;

    public Token $constant;

    public function __construct(TypeNode $name, Token $doubleColon, Token $constant)
    {
        $this->name = $name;
        $this->doubleColon = $doubleColon;
        $this->constant = $constant;
    }
}
