<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\TypeList;
use Phpactor\DocblockParser\Ast\TypeNode;

class IntersectionNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'types',
    ];

    public function __construct(public TypeList $types)
    {
    }
}
