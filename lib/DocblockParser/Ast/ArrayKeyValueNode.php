<?php

namespace Phpactor\DocblockParser\Ast;

class ArrayKeyValueNode extends Node
{
    protected const CHILD_NAMES = [
        'key',
        'colon',
        'type',
    ];

    public ?Token $key;

    public ?Token $colon;

    public ?TypeNode $type;

    public function __construct(?Token $key, ?Token $colon, ?TypeNode $type)
    {
        $this->key = $key;
        $this->colon = $colon;
        $this->type = $type;
    }
}
