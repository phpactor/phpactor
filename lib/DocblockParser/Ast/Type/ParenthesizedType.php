<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ParenthesizedType extends TypeNode
{
    protected const CHILD_NAMES = [
        'open',
        'node',
        'closed',
    ];

    public function __construct(
        public Token $open,
        public ?TypeNode $node,
        public ?Token $closed
    ) {
    }
}
