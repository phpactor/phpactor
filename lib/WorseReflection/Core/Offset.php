<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\TextDocument\ByteOffset;
use InvalidArgumentException;

final class Offset
{
    private $offset;

    private function __construct(int $offset)
    {
        if ($offset < 0) {
            throw new InvalidArgumentException(sprintf(
                'Offset cannot be negative! Got "%s"',
                $offset
            ));
        }

        $this->offset = $offset;
    }

    /**
     * @param Offset|ByteOffset|int $value
     */
    public static function fromUnknown($value): self
    {
        if ($value instanceof ByteOffset) {
            return self::fromInt($value->toInt());
        }

        if ($value instanceof Offset) {
            return $value;
        }

        if (is_int($value)) {
            return self::fromInt($value);
        }

        /** @phpstan-ignore-next-line */
        throw new InvalidArgumentException(sprintf(
            'Do not know how to create offset from type "%s"',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    public static function fromInt(int $offset): Offset
    {
        return new self($offset);
    }

    public function toInt(): int
    {
        return $this->offset;
    }
}
