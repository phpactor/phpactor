<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Type;

use Phpactor\WorseReflection\DocblockParser\Ast\Token;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;

class ThisNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'name'
    ];
    
    public Token $name;

    public function __construct(Token $name)
    {
        $this->name = $name;
    }
}
