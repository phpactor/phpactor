<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

interface DefinitionLocator
{
    /**
     * Provide the Location (URI and byte offset) for the text under the cursor.
     *
     * If this locator cannot provide a definition it MUST throw a
     * CouldNotLocateDefinition exception.
     *
     * @throws CouldNotLocateDefinition
     */
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations;
}
