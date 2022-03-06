<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TestDefinitionLocator implements DefinitionLocator
{
    /**
     * @var DefinitionLocation
     */
    private $location;

    public function __construct(?DefinitionLocation $location)
    {
        $this->location = $location;
    }

    public static function fromLocation(DefinitionLocation $location): self
    {
        return new self($location);
    }

    /**
     * {@inheritDoc}
     */
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): DefinitionLocation
    {
        if (null === $this->location) {
            throw new CouldNotLocateDefinition(
                'Definition not found'
            );
        }

        return $this->location;
    }
}
