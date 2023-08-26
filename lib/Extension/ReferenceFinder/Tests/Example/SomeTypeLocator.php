<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type\MixedType;

class SomeTypeLocator implements TypeLocator
{
    private const EXAMPLE_PATH = '/foobar';
    private const EXAMPLE_OFFSET = 1;

    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        return new TypeLocations([new TypeLocation(
            new MixedType(),
            Location::fromPathAndOffsets(self::EXAMPLE_PATH, self::EXAMPLE_OFFSET, self::EXAMPLE_OFFSET),
        )]);
    }
}
