<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\ArrayKeyValueList;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ArrayShapeNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'open',
        'arrayKeyValueList',
        'close',
    ];

    public Token $open;

    public ArrayKeyValueList $arrayKeyValueList;

    public ?Token $close;

    public function __construct(Token $open, ArrayKeyValueList $arrayKeyValueList, ?Token $close)
    {
        $this->open = $open;
        $this->arrayKeyValueList = $arrayKeyValueList;
        $this->close = $close;
    }
}
