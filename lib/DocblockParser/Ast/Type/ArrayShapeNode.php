<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ArrayShapeNode extends TypeNode
{
    public Token $open;
    public ParamList $paramList;
    public Token $close;

    public function __construct(Token $open, ParamList $paramList, Token $close)
    {
        $this->open = $open;
        $this->paramList = $paramList;
        $this->close = $close;
    }
}
