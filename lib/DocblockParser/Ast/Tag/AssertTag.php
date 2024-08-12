<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\VariableNode;

class AssertTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'negationOrEquality',
        'type',
        'paramName',
    ];

    public function __construct(
        public Token $tag,
        public ?Token $negationOrEquality,
        public ?TypeNode $type,
        public ?VariableNode $paramName = null
    ) {
    }
}
