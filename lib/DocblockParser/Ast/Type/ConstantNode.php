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

    public function __construct(
        public TypeNode $name,
        public Token $doubleColon,
        public Token $constant
    ) {
    }
}
