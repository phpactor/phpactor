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

    public static function fromUnknown(Offset|ByteOffset|int $value): self
    {
        if ($value instanceof ByteOffset) {
            return self::fromInt($value->toInt());
        }

        if ($value instanceof Offset) {
            return $value;
        }

        return self::fromInt($value);
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
