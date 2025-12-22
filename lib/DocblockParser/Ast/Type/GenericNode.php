<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Element;
use Phpactor\DocblockParser\Ast\TypeList;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Token;

class GenericNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'type',
        'open',
        'parameters',
        'close'
    ];

    /**
     * @param TypeList<Element> $parameters
     */
    public function __construct(
        public Token $open,
        public TypeNode $type,
        public TypeList $parameters,
        public Token $close
    ) {
    }

    public function close(): Token
    {
        return $this->close;
    }

    public function open(): Token
    {
        return $this->open;
    }

    /**
     * @return TypeList<Element>
     */
    public function parameters(): TypeList
    {
        return $this->parameters;
    }

    public function type(): TypeNode
    {
        return $this->type;
    }
}
