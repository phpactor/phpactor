<?php

namespace Phpactor\TextDocument;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * @implements IteratorAggregate<LocationRange>
 */
final class LocationRanges implements IteratorAggregate, Countable
{
    /**
     * @var LocationRange[]
     */
    private array $locationRanges = [];

    /**
     * @param iterable<LocationRange> $locationRanges
     */
    public function __construct(iterable $locationRanges)
    {
        foreach ($locationRanges as $location) {
            $this->add($location);
        }
    }

    /**
     * @return ArrayIterator<int,LocationRange>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->locationRanges);
    }

    /**
     * @param iterable<LocationRange> $locationRanges
     */
    public function append(iterable $locationRanges): self
    {
        $newLocations = $this->locationRanges;
        foreach ($locationRanges as $location) {
            $newLocations[] = $location;
        }

        return new self($newLocations);
    }

    public function count(): int
    {
        return count($this->locationRanges);
    }

    public function first(): LocationRange
    {
        if (count($this->locationRanges) === 0) {
            throw new RuntimeException(
                'There are no locationRanges in this collection'
            );
        }

        return reset($this->locationRanges);
    }

    private function add(LocationRange $location): self
    {
        $this->locationRanges[] = $location;

        return $this;
    }

    public function sorted(): self
    {
        $sortedLocations = $this->locationRanges;

        usort($sortedLocations, function (LocationRange $first, LocationRange $second) {
            $order = strcmp((string) $first->uri(), (string) $second->uri());
            if (0 !== $order) {
                return $order;
            }

            return $first->range()->start()->toInt() - $second->range()->start()->toInt();
        });

        return new self($sortedLocations);
    }
}
