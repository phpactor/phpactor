<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<Diagnostic>
 */
final class Diagnostics implements IteratorAggregate, Countable
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

    /**
     * @param class-string $classFqn
     */
    public function byClass(string $classFqn): self
    {
        return new self(array_filter($this->diagnostics, fn (Diagnostic $d) => $d instanceof $classFqn));
    }

    public function at(int $index): Diagnostic
    {
        if (!isset($this->diagnostics[$index])) {
            throw new RuntimeException(sprintf(
                'Diagnostic at index "%s" does not exist',
                $index
            ));
        }

        return $this->diagnostics[$index];
    }
}
