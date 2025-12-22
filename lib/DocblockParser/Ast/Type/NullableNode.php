<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Token;

class NullableNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'nullable',
        'type',
    ];

    public function __construct(
        public Token $nullable,
        public TypeNode $type
    ) {
    }

    public function nullable(): Token
    {
        return $this->nullable;
    }

    public function type(): TypeNode
    {
        return $this->type;
    }
}
