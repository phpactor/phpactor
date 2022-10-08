<?php

class UnitEnumCase {
    public string $name;
}

class BackedEnumCase extends UnitEnumCase {
    /** @var int|string */
    public $value;
}

interface UnitEnum
{
    /**
     * @return UnitEnumCase[]
     */
    public static function cases(): array;
}

interface BackedEnum extends UnitEnum
{
    /**
     * @return BackedEnumCase[]
     */
    public static function cases(): array;

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

