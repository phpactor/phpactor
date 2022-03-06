<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;

class SomeTypeLocator implements TypeLocator
{
    const EXAMPLE_OFFSET = 1;
    const EXAMPLE_PATH = '/foobar';

    /**
     * {@inheritDoc}
     */
    public function locateType(TextDocument $document, ByteOffset $byteOffset): Location
    {
        return new Location(
            TextDocumentUri::fromString(self::EXAMPLE_PATH),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );
    }
}
