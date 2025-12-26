<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TestTypeLocator implements TypeLocator
{
    public function __construct(private readonly TypeLocations $locations)
    {
    }

    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        return $this->locations;
    }
}
