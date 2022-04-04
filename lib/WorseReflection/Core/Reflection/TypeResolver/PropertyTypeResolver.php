<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;

class PropertyTypeResolver
{
    private ReflectionProperty $property;
    
    public function __construct(ReflectionProperty $property)
    {
        $this->property = $property;
    }

    public function resolve(): Type
    {
        $docblockTypes = $this->getDocblockTypes();

        if (0 === $docblockTypes->count()) {
            $docblockTypes = $this->getDocblockTypesFromClass();
        }

        $resolvedTypes = array_map(function (Type $type) {
            return $this->property->scope()->resolveFullyQualifiedName($type, $this->property->class());
        }, iterator_to_array($docblockTypes));

        foreach ($resolvedTypes as $resolvedType) {
            return $resolvedType;
        }

        return TypeFactory::undefined();
    }

    private function getDocblockTypes(): Types
    {
        return $this->property->docblock()->vars()->types();
    }

    private function getDocblockTypesFromClass()
    {
        return $this->property->class()->docblock()->propertyTypes($this->property->name());
    }
}
