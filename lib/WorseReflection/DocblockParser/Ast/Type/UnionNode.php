<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Type;

use Phpactor\WorseReflection\DocblockParser\Ast\TypeList;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;

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
