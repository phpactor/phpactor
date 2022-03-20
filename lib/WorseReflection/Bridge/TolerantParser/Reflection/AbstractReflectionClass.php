<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;

abstract class AbstractReflectionClass extends AbstractReflectedNode implements ReflectionClassLike
{
    /**
     * @deprecated Use instanceof instead
     */
    public function isInterface(): bool
    {
        return $this instanceof ReflectionInterface;
    }

    /**
     * @deprecated Use instanceof instead
     */
    public function isTrait(): bool
    {
        return $this instanceof ReflectionTrait;
    }

    public function isEnum(): bool
    {
        return $this instanceof ReflectionEnum;
    }

    /**
     * @deprecated Use instanceof instead
     */
    public function isClass(): bool
    {
        return $this instanceof ReflectionClass;
    }

    public function isConcrete(): bool
    {
        return false;
    }

    public function deprecation(): Deprecation
    {
        return $this->docblock()->deprecation();
    }
}
