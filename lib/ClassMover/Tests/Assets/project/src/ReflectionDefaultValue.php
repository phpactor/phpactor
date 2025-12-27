<?php

namespace Acme\Foobar\Reflection;

final class ReflectionDefaultValue
{
    private bool $undefined = false;

    private function __construct(private readonly mixed $value = null)
    {
    }

    public static function fromValue(mixed $value): ReflectionDefaultValue
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
        return !$this->undefined;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
