<?php

namespace Phpactor\DocblockParser\Ast\Tag;

class InvertedVarTag extends VarTag
{
    protected const CHILD_NAMES = [
        'tag',
        'variable',
        'type',
    ];
}
