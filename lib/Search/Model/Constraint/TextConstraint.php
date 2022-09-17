<?php

namespace Phpactor\Search\Model\Constraint;

final class TextConstraint extends AbstractConstraint
{
    public function text(): string
    {
        return $this->value;
    }
}
