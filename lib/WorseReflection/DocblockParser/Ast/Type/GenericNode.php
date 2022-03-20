<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Type;

use Phpactor\WorseReflection\DocblockParser\Ast\Element;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeList;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;

class GenericNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'type',
        'open',
        'parameters',
        'close'
    ];
    
    public Token $open;
    
    public Token $close;

    /**
     * @var TypeList<Element>
     */
    public TypeList $parameters;
    
    public TypeNode $type;

    /**
     * @param TypeList<Element> $parameters
     */
    public function __construct(Token $open, TypeNode $type, TypeList $parameters, Token $close)
    {
        $this->open = $open;
        $this->close = $close;
        $this->parameters = $parameters;
        $this->type = $type;
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
