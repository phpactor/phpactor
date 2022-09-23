<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;

class MethodDeclarationContext extends NodeContext
{
    private Symbol $symbol;
    private Type $type;

    public function __construct(Symbol $symbol, Type $type, Type $containerType)
    {
        parent::__construct($symbol, $type, $containerType);
    }
}
