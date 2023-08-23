<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Type\MixedType;

class SomeTypeLocator implements TypeLocator
{
    const EXAMPLE_OFFSET = 1;
    const EXAMPLE_PATH = '/foobar';


    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        return new TypeLocations([new TypeLocation(new MixedType(), new LocationRange(
            TextDocumentUri::fromString(self::EXAMPLE_PATH),
            ByteOffsetRange::fromInts(self::EXAMPLE_OFFSET, self::EXAMPLE_OFFSET),
        ))]);
    }
}
