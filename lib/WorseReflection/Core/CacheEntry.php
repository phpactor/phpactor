<?php

namespace Phpactor\WorseReflection\Core;

final class CacheEntry
{
    public function __construct(private mixed $value)
    {
    }

    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * This method is not safe: it does validate or cast
     * the value as/to a scalar.
     *
     * @return scalar
     */
    public function scalar(): mixed
    {
        /** @phpstan-ignore-next-line */
        return $this->value;
    }

    public function string(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->value;
    }

    /**
     * This method is not safe: it does validate or cast
     * the value as/to an object.
     *
     * @template TObject of object
     * @param class-string<TObject> $type
     * @return TObject
     */
    public function expect(string $type): object
    {
        /** @phpstan-ignore-next-line */
        return $this->value;
    }
}
