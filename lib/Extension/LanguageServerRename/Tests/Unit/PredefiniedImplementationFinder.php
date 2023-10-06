<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit;

use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocument;

class PredefiniedImplementationFinder implements ClassImplementationFinder
{
    public function __construct(private Locations $locations)
    {
    }

    public function findImplementations(
        TextDocument $document,
        ByteOffset $byteOffset,
        bool $includeDefinition = false
    ): Locations {
        return $this->locations;
    }
}
