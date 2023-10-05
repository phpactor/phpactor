<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;

class SomeDefinitionLocator implements DefinitionLocator
{
    public const EXAMPLE_PATH = '/path/to.php';
    public const EXAMPLE_OFFSET = 666;
    public const EXAMPLE_OFFSET_END = 777;

    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        return new TypeLocations([
            new TypeLocation(
                TypeFactory::mixed(),
                Location::fromPathAndOffsets(self::EXAMPLE_PATH, self::EXAMPLE_OFFSET, self::EXAMPLE_OFFSET_END)
            )
        ]);
    }
}
