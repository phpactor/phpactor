<?php

namespace Phpactor\Search\Model\Constraint;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class TypeConstraint extends AbstractConstraint
{
    public function type(): Type
    {
        return TypeFactory::fromString($this->value);
    }

    public function describe(): string
    {
        return sprintf('having type: %s', $this->value);
    }
}
