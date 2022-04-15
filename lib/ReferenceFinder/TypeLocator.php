<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;

interface TypeLocator
{
    /**
     * Provide the Location (URI and byte offset) for the text under the
     * cursor.
     *
     * If this locator cannot provide a location it MUST throw a
     * CouldNotLocateType exception.
     */
    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations;
}
