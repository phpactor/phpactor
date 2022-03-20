<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Type;

use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;

class ListNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'type',
        'listChars',
    ];
    
    public TypeNode $type;
    
    public Token $listChars;

    public function __construct(TypeNode $type, Token $listChars)
    {
        $this->type = $type;
        $this->listChars = $listChars;
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
