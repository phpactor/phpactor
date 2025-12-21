<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Token;

class ListBracketsNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'type',
        'listChars',
    ];

    public function __construct(
        public TypeNode $type,
        public Token $listChars
    ) {
    }

    public function type(): TypeNode
    {
        return $this->type;
    }

    public function listChars(): Token
    {
        return $this->listChars;
    }
}
