<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TestDefinitionLocator implements DefinitionLocator
{
    private ?TypeLocations $location;

    public function __construct(?TypeLocations $locations)
    {
        $this->location = $location;
    }

    public static function fromLocation(DefinitionLocation $location): self
    {
        return new self($location);
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
