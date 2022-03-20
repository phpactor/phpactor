<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Psr\Log\LoggerInterface;

class PropertyTypeResolver
{
    private ReflectionProperty $property;
    
    private LoggerInterface $logger;

    public function __construct(ReflectionProperty $property, LoggerInterface $logger)
    {
        $this->property = $property;
        $this->logger = $logger;
    }

    public function resolve(): Types
    {
        $docblockTypes = $this->getDocblockTypes();

        if (0 === $docblockTypes->count()) {
            $docblockTypes = $this->getDocblockTypesFromClass();
        }

        $resolvedTypes = array_map(function (Type $type) {
            return $this->property->scope()->resolveFullyQualifiedName($type, $this->property->class());
        }, iterator_to_array($docblockTypes));

        return Types::fromTypes($resolvedTypes);
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
