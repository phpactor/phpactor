<?php

namespace Phpactor\DocblockParser\Ast\Type;

use Phpactor\DocblockParser\Ast\TypeList;
use Phpactor\DocblockParser\Ast\TypeNode;

class UnionNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'types',
    ];
    
    public TypeList $types;

    public function __construct(TypeList $types)
    {
        $this->types = $types;
    }
}
