<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
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
        $docblockType = $this->getDocblockTypes()->best();

        if (false === TypeUtil::isDefined($docblockType)) {
            $docblockType = $this->getDocblockTypesFromClass();
        }

        if (TypeUtil::isDefined($docblockType)) {
            return $this->property->scope()->resolveFullyQualifiedName($docblockType, $this->property->class());
        }

        return $this->property->type();
    }

    private function getDocblockTypes(): Types
    {
        return $this->property->docblock()->vars()->types();
    }

    private function getDocblockTypesFromClass(): Type
    {
        return $this->property->class()->docblock()->propertyType($this->property->name());
    }
}
