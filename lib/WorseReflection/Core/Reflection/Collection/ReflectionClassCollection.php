<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

final class ReflectionClassCollection extends AbstractReflectionCollection
{
    public function concrete(): self
    {
        return new static(array_filter($this->items, function ($item) {
            return $item->isConcrete();
        }));
    }
}
