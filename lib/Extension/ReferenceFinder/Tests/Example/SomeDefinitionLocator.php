<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\TypeFactory;

class SomeDefinitionLocator implements DefinitionLocator
{
    const EXAMPLE_PATH = '/path/to.php';
    const EXAMPLE_OFFSET = 666;

    
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        return new TypeLocations([
            new TypeLocation(
                TypeFactory::mixed(),
                new Location(
                    TextDocumentUri::fromString(self::EXAMPLE_PATH),
                    ByteOffset::fromInt(self::EXAMPLE_OFFSET)
                )
            )
        ]);
    }
}
