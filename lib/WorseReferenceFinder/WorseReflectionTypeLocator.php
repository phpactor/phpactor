<?php

namespace Phpactor\WorseReferenceFinder;

use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionTypeLocator implements TypeLocator
{
    public function __construct(private Reflector $reflector)
    {
    }


    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        if (false === $document->language()->isPhp()) {
            throw new UnsupportedDocument('I only work with PHP files');
        }

        if ($uri = $document->uri()) {
            $sourceCode = SourceCode::fromPathAndString($uri->__toString(), $document->__toString());
        } else {
            $sourceCode = SourceCode::fromString($document->__toString());
        }

        $type = $this->reflector->reflectOffset(
            $sourceCode,
            $byteOffset->toInt()
        )->symbolContext()->type();

        $typeLocations = [];
        foreach ($type->expandTypes() as $type) {
            if ($type instanceof ArrayType) {
                $type = $type->iterableValueType();
            }

            if (!$type instanceof ClassType) {
                continue;
            }
            $typeLocations[] = new TypeLocation($type, $this->gotoType($type));
        }

        return new TypeLocations($typeLocations);
    }

    private function gotoType(Type $type): Location
    {
        $className = $this->resolveClassName($type);

        try {
            $class = $this->reflector->reflectClassLike($className->full());
        } catch (NotFound $e) {
            throw new CouldNotLocateType($e->getMessage(), 0, $e);
        }

        $path = $class->sourceCode()->path();

        return new Location(
            TextDocumentUri::fromString($path),
            ByteOffset::fromInt($class->position()->start())
        );
    }

    private function resolveClassName(Type $type): ClassName
    {
        foreach ($type->expandTypes()->classLike() as $type) {
            return $type->name();
        }

        throw new CouldNotLocateType(sprintf(
            'Cannot goto to primitive type %s "%s"',
            get_class($type),
            $type->__toString()
        ));
    }
}
