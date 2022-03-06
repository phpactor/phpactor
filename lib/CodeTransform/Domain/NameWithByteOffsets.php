<?php

namespace Phpactor\CodeTransform\Domain;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<NameWithByteOffset>
 */
class NameWithByteOffsets implements IteratorAggregate
{
    private $nameWithByteOffsets;

    public function __construct(NameWithByteOffset ...$nameWithByteOffsets)
    {
        $this->nameWithByteOffsets = $nameWithByteOffsets;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->nameWithByteOffsets);
    }

    public function onlyUniqueNames(): self
    {
        $seen = [];
        return new self(...array_filter($this->nameWithByteOffsets, function (NameWithByteOffset $byteOffset) use (&$seen) {
            $name = $byteOffset->name()->__toString();
            if (in_array($name, $seen)) {
                return false;
            }
            $seen[] = $name;
            return true;
        }));
    }
}
