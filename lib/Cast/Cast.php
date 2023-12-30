<?php

namespace Phpactor\Cast;

use RuntimeException;

class Cast
{
    /**
     * @param mixed $value
     */
    public static function toString($value): string
    {
        if (!is_string($value)) {
            throw new RuntimeException(sprintf(
                'Expected string, got "%s"',
                gettype($value)
            ));
        }
        return $value;
    }

    /**
     * @param mixed $value
     */
    public static function toStringOrNull($value = null): ?string
    {
        if (null === $value) {
            return $value;
        }

        return self::toString($value);
    }

    /**
     * @param mixed $value
     */
    public static function toInt($value): int
    {
        if (!is_numeric($value)) {
            throw new RuntimeException(sprintf(
                'Cannot cast "%s" to int',
                gettype($value)
            ));
        }
        return (int) $value;
    }

    /**
     * @param mixed $value
     */
    public static function toIntOrNull($value): ?int
    {
        if (null === $value) {
            return null;
        }

        return self::toInt($value);
    }

    /**
     * @param mixed $value
     */
    public static function toBool($value): bool
    {
        return (bool) $value;
    }

    /**
     * @param mixed $value
     * @return array<mixed>
     */
    public static function toArray($value): array
    {
        return (array) $value;
    }
}
