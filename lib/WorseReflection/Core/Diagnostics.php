<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Diagnostic>
 */
class Diagnostics implements IteratorAggregate, Countable
{
    /**
     * @var Diagnostic[]
     */
    private array $diagnostics;

    /**
     * @param Diagnostic[] $diagnostics
     */
    public function __construct(array $diagnostics)
    {
        $this->diagnostics = $diagnostics;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->diagnostics);
    }

    public function count(): int
    {
        return count($this->diagnostics);
    }
}
