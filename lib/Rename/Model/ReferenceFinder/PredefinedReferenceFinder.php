<?php

namespace Phpactor\Rename\Model\ReferenceFinder;

use Generator;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class PredefinedReferenceFinder implements ReferenceFinder
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
    }
}
