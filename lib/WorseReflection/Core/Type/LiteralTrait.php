<?php

namespace Phpactor\WorseReflection\Core\Type;

trait LiteralTrait
{
    public function withValue(mixed $value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }
}
