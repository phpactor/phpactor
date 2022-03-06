<?php

namespace Phpactor\Name;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Names implements Countable, IteratorAggregate
{
    /**
     * @var array
     */
    private $names;

    private function __construct(Name ...$names)
    {
        $this->names = $names;
    }

    public static function fromNames(array $array)
    {
        return new self(...$array);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->names);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->names);
    }
}
