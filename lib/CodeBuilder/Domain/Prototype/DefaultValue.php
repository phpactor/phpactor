<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class DefaultValue extends Value
{
    private bool $none = false;

    public static function none(): self
    {
        $new = new static();
        $new->none = true;

        return $new;
    }

    public static function null(): DefaultValue
    {
        return new static(null);
    }

    public function notNone(): bool
    {
        return !$this->none;
    }
}
