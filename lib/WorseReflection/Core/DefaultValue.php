<?php

namespace Phpactor\WorseReflection\Core;

final class DefaultValue
{
    private $value;

    private $undefined = false;

    private function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function fromValue($value): DefaultValue
    {
        return new self($value);
    }

    public static function undefined(): DefaultValue
    {
        $new = new self();
        $new->undefined = true;

        return $new;
    }

    public function isDefined(): bool
    {
        return false === $this->undefined;
    }

    public function value()
    {
        return $this->value;
    }
}
