<?php

namespace Phpactor\ReferenceFinder;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Traversable;

/**
 * @implements IteratorAggregate<TypeLocation>
 */
class TypeLocations implements IteratorAggregate
{
    /**
     * @var TypeLocation[]
     */
    private array $typeLocations;

    /**
     * @param TypeLocation[] $typeLocations
     */
    public function __construct(array $typeLocations)
    {
        $this->typeLocations = $typeLocations;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->typeLocations);
    }

    public function first(): TypeLocation
    {
        if (!$this->typeLocations) {
            throw new CouldNotLocateType(
                'There are no type locations, cannot get the first'
            );
        }

        return reset($this->typeLocations);
    }

    public function atIndex(int $index): TypeLocation
    {
        if (!isset($this->typeLocations[$index])) {
            throw new CouldNotLocateType(sprintf(
                'There are no type locations at index "%s"',
                $index
            ));
        }

        return $this->typeLocations[$index];
    }

    public function count(): int
    {
        return count($this->typeLocations);
    }

    public function byTypeName(string $typeName): TypeLocation
    {
        foreach ($this->typeLocations as $typeLocation) {
            if ($typeLocation->type()->__toString() === $typeName) {
                return $typeLocation;
            }
        }
        throw new CouldNotLocateType(sprintf(
            'Unknown type name "%s"',
            $typeName
        ));
    }

    public static function forLocation(TypeLocation $location): self
    {
        return new self([$location]);
    }
}
