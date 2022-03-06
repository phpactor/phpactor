<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocument;

class SomeReferenceFinder implements ReferenceFinder
{
    /**
     * {@inheritDoc}
     */
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Locations
    {
        return new Locations([]);
    }
}
