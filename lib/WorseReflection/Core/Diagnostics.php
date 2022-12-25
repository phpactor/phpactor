<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Phpactor\TextDocument\ByteOffsetRange;
use RuntimeException;
use Traversable;

/**
 * @template T of Diagnostic
 * @implements IteratorAggregate<T>
 */
final class Diagnostics implements IteratorAggregate, Countable
{
    /**
     * @param Diagnostic[] $diagnostics
     */
    public function __construct(private array $diagnostics)
    {
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
     * @template TD of Diagnostic
     * @param class-string<TD> $classFqn
     * @return Diagnostics<TD>
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

    public function withinRange(ByteOffsetRange $byteOffsetRange): self
    {
        return new self(array_filter(
            $this->diagnostics,
            fn (Diagnostic $d) =>
            $d->range()->start()->toInt() >= $byteOffsetRange->start()->toInt() &&
            $d->range()->end()->toInt() <= $byteOffsetRange->end()->toInt()
        ));
    }

    public function containingRange(ByteOffsetRange $byteOffsetRange): self
    {
        return new self(array_filter(
            $this->diagnostics,
            fn (Diagnostic $d) =>
            $d->range()->start()->toInt() <= $byteOffsetRange->start()->toInt() &&
            $d->range()->end()->toInt() >= $byteOffsetRange->end()->toInt()
        ));
    }
}
