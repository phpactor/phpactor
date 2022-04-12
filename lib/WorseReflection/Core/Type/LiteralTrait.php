<?php

namespace Phpactor\WorseReflection\Core\Type;

trait LiteralTrait
{
    public function withValue($value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }
}
