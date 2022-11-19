<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty as PhpactorReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;

/**
 * @extends HomogeneousReflectionMemberCollection<PhpactorReflectionProperty>
 */
final class ReflectionPropertyCollection extends HomogeneousReflectionMemberCollection
{
    /**
     * @param PhpactorReflectionProperty[] $properties
     */
    public static function fromReflectionProperties(array $properties): CoreReflectionPropertyCollection
    {
        $items = [];
        foreach ($properties as $property) {
            $items[$property->name()] = $property;
        }

        return new self($items);
    }
}
