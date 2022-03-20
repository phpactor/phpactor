<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

class VirtualReflectionMethodCollection extends VirtualReflectionMemberCollection implements ReflectionMethodCollection
{
    public static function fromReflectionMethods(array $reflectionMethods): self
    {
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methods[$reflectionMethod->name()] = $reflectionMethod;
        }
        return new self($methods);
    }

    public function abstract(): ReflectionMethodCollection
    {
        return new self(array_filter($this->items, function (ReflectionMethod $item) {
            return $item->isAbstract();
        }));
    }

    protected function collectionType(): string
    {
        return ReflectionMethodCollection::class;
    }
}
