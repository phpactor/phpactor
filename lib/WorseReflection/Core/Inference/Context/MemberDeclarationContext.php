<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;

class MemberDeclarationContext extends NodeContext
{
    public function __construct(Symbol $symbol, Type $type, ClassType $containerType)
    {
        parent::__construct($symbol, $type, $containerType);
    }

    public function classType(): ClassType
    {
        return $this->containerType;
    }

    public function name(): string
    {
        return $this->symbol->name();
    }
}
