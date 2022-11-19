<?php

namespace Acme\Foobar\Reflection;

final class ReflectionDefaultValue
{
    private $undefined = false;

    private function __construct(private $value = null)
    {
    }

    public static function fromValue($value): ReflectionDefaultValue
    {
        return new self($value);
    }

    public static function undefined(): ReflectionDefaultValue
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
