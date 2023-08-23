<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit;

use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\TextDocument;

class PredefiniedImplementationFinder implements ClassImplementationFinder
{
    public function __construct(private LocationRanges $locations)
    {
    }

    public function findImplementations(
        TextDocument $document,
        ByteOffset $byteOffset,
        bool $includeDefinition = false
    ): LocationRanges {
        return $this->locations;
    }
}
