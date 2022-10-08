<?php

/**
 * @property string $name
 */
interface UnitEnum
{
    /**
     * @return static[]
     */
    public static function cases(): array;
}

/**
 * @property int|string $value
 */
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

