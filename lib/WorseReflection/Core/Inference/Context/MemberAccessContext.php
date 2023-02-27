<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use RuntimeException;

class MemberAccessContext extends NodeContext
{
    public function __construct(Symbol $symbol, Type $type, Type $containerType)
    {
        parent::__construct($symbol, $type, $containerType);
    }

    public function classType(): ?ClassType
    {
        if (!$this->containerType instanceof ClassType) {
            throw new RuntimeException('Member declaration must have class as a container type');
        }
        return $this->containerType;
    }

    public function name(): string
    {
        return $this->symbol->name();
    }
}
