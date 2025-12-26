<?php

namespace Phpactor\Rename\Model\ReferenceFinder;

use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Generator;

class PredefinedReferenceFinderFoo implements ReferenceFinder
{
    /**
     * @var PotentialLocation[]
     */
    private readonly array $locations;

    public function __construct(PotentialLocation ...$locations)
    {
        $this->locations = $locations;
    }

    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        foreach ($this->locations as $location) {
            yield $location;
        }

        return true;
    }
}
