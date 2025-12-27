<?php

namespace Phpactor\Name;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Names implements Countable, IteratorAggregate
{
    private readonly array $names;

    private function __construct(Name ...$names)
    {
        $this->names = $names;
    }

    public static function fromNames(array $array)
    {
        return new self(...$array);
    }


    public function count(): int
    {
        return count($this->names);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->names);
    }
}
