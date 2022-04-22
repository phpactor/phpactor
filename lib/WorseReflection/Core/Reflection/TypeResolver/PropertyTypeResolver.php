<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

class PropertyTypeResolver
{
    private ReflectionProperty $property;
    
    public function __construct(ReflectionProperty $property)
    {
        $this->property = $property;
    }

    public function resolve(): Type
    {
        $docblockType = $this->getDocblockType();

        if (false === TypeUtil::isDefined($docblockType)) {
            $docblockType = $this->getDocblockTypesFromClass();
        }

        if (TypeUtil::isDefined($docblockType)) {
            return $docblockType;
        }

        return $this->property->type();
    }

    private function getDocblockType(): Type
    {
        return $this->property->docblock()->vars()->type();
    }

    private function getDocblockTypesFromClass(): Type
    {
        return $this->property->class()->docblock()->propertyType($this->property->name());
    }
}
