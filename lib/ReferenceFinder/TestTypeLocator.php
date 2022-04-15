<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TestTypeLocator implements TypeLocator
{
    private TypeLocations $locations;

    public function __construct(TypeLocations $locations)
    {
        $this->locations = $locations;
    }

    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        return $this->locations;
    }
}
