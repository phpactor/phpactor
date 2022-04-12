<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\ArrayKeyValueList;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ArrayShapeNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'open',
        'paramList',
        'close',
    ];

    public Token $open;
    public ArrayKeyValueList $paramList;
    public Token $close;

    public function __construct(Token $open, ArrayKeyValueList $paramList, ?Token $close)
    {
        $this->open = $open;
        $this->paramList = $paramList;
        $this->close = $close;
    }
}
