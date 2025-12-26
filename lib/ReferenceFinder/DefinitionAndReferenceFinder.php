<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DefinitionAndReferenceFinder implements ReferenceFinder
{
    public function __construct(
        private readonly DefinitionLocator $locator,
        private readonly ReferenceFinder $referenceFinder
    ) {
    }


    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        try {
            $location = $this->locator->locateDefinition($document, $byteOffset);
            yield PotentialLocation::surely($location->first()->location());
        } catch (CouldNotLocateDefinition) {
        }

        $generator = $this->referenceFinder->findReferences($document, $byteOffset);
        foreach ($generator as $reference) {
            yield $reference;
        }
        return $generator->getReturn();
    }
}
