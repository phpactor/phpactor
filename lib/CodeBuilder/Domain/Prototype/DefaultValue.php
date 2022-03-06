<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class DefaultValue extends Value
{
    private $none = false;

    public static function none()
    {
        $new = new static();
        $new->none = true;

        return $new;
    }

    public static function null(): DefaultValue
    {
        return new static(null);
    }

    public function notNone()
    {
        return false === $this->none;
    }
}
