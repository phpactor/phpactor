<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TestReferenceFinder implements ReferenceFinder
{
    /**
     * @var PotentialLocation[]
     */
    private array $locations;

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
