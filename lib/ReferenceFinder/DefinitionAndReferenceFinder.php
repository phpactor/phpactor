<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DefinitionAndReferenceFinder implements ReferenceFinder
{
    /**
     * @var DefinitionLocator
     */
    private $locator;
    /**
     * @var ReferenceFinder
     */
    private $referenceFinder;

    public function __construct(DefinitionLocator $locator, ReferenceFinder $referenceFinder)
    {
        $this->locator = $locator;
        $this->referenceFinder = $referenceFinder;
    }

    /**
     * {@inheritDoc}
     */
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        try {
            $location = $this->locator->locateDefinition($document, $byteOffset);
            yield PotentialLocation::surely($location);
        } catch (CouldNotLocateDefinition $notFound) {
        }

        foreach ($this->referenceFinder->findReferences($document, $byteOffset) as $reference) {
            yield $reference;
        }
    }
}
