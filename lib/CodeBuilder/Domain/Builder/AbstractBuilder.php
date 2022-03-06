<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Generator;
use RuntimeException;

abstract class AbstractBuilder implements Builder
{
    private $originalProperties = [];

    public function snapshot(): void
    {
        $propertyValues = [];
        foreach ($this as $propertyName => $property) {
            if ($propertyName == 'originalProperties') {
                continue;
            }
            $propertyValues[$propertyName] = is_object($this->$propertyName) ? clone $this->$propertyName : $this->$propertyName;
        }

        $this->originalProperties = $propertyValues;

        foreach ($this->children() as $child) {
            $child->snapshot();
        }
    }

    public function isModified(): bool
    {
        if (empty($this->originalProperties)) {
            return true;
        }

        foreach ($this->originalProperties as $propertyName => $propertyValue) {
            if ($this->$propertyName != $propertyValue) {
                return true;
            }
        }

        foreach ($this->children() as $child) {
            if ($child->isModified()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Generator<Builder>
     */
    public function children(): Generator
    {
        foreach (static::childNames() as $childName) {
            $children = (array) $this->$childName;

            foreach ($children as $child) {
                if (!$child instanceof Builder) {
                    throw new RuntimeException(sprintf(
                        'Child "%s" is not a builder instance, it is a "%s"',
                        $childName,
                        is_object($child) ? get_class($child) : gettype($child)
                    ));
                }

                yield $child;
            }
        }
    }
}
