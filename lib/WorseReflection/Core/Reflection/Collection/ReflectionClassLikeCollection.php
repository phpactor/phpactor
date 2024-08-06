<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as PhpactorReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

/**
 * @extends AbstractReflectionCollection<ReflectionClassLike>
 */
final class ReflectionClassLikeCollection extends AbstractReflectionCollection
{
    public function classes(): ReflectionClassCollection
    {
        /** @phpstan-ignore-next-line */
        return new ReflectionClassCollection(iterator_to_array($this->byMemberClass(PhpactorReflectionClass::class)));
    }

    public function concrete(): self
    {
        return new static(array_filter($this->items, function (ReflectionClassLike $item) {
            return $item->isConcrete();
        }));
    }
}
