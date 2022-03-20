<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Type;

use Phpactor\WorseReflection\DocblockParser\Ast\TypeList;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;

class CallableNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'name',
        'open',
        'parameters',
        'close',
        'colon',
        'type'
    ];

    public ?Token $name;

    public ?Token $open;

    public ?TypeList $parameters;

    public ?Token $close;

    public ?Token $colon;

    public ?TypeNode $type;

    public function __construct(
        Token $name,
        ?Token $open,
        ?TypeList $parameters,
        ?Token $close,
        ?Token $colon,
        ?TypeNode $type
    ) {
        $this->name = $name;
        $this->open = $open;
        $this->parameters = $parameters;
        $this->close = $close;
        $this->type = $type;
        $this->colon = $colon;
    }
}
