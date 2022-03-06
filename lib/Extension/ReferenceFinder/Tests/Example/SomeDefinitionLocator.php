<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;

class SomeDefinitionLocator implements DefinitionLocator
{
    const EXAMPLE_PATH = '/path/to.php';
    const EXAMPLE_OFFSET = 666;

    /**
     * {@inheritDoc}
     */
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): DefinitionLocation
    {
        return new DefinitionLocation(
            TextDocumentUri::fromString(self::EXAMPLE_PATH),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );
    }
}
