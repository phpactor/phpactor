<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type;

class TestDefinitionLocator implements DefinitionLocator
{
    public function __construct(private ?TypeLocations $location)
    {
    }

    public static function fromSingleLocation(Type $type, ?LocationRange $range): self
    {
        if (null === $range) {
            return new self(null);
        }
        return new self(new TypeLocations([ new TypeLocation($type, $range)]));
    }

    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        if (null === $this->location) {
            throw new CouldNotLocateDefinition(
                'Definition not found'
            );
        }

        return $this->location;
    }
}
