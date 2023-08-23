<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\TextDocument;

class SomeImplementationFinder implements ClassImplementationFinder
{
    public function findImplementations(TextDocument $document, ByteOffset $byteOffset, bool $includeDefinition = false): LocationRanges
    {
        return new LocationRanges([]);
    }
}
