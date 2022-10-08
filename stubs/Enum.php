<?php

class BackedEnumCase {
    public string $name;
    /** @var int|string */
    public $value;
}

interface UnitEnum
{
    /**
     * @return static[]
     */
    public static function cases(): array;
}

interface BackedEnum extends UnitEnum
{
    /**
     * @param int|string $value
     * @return static
     */
    public static function from($value): static;

    /**
     * @param int|string $value
     * @return static|null
     */
    public static function tryFrom($value): ?static;
}

