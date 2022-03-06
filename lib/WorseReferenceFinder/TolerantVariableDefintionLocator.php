<?php

namespace Phpactor\WorseReferenceFinder;

use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use function assert;

class TolerantVariableDefintionLocator implements DefinitionLocator
{
    /**
     * @var TolerantVariableReferenceFinder
     */
    private $finder;

    public function __construct(TolerantVariableReferenceFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritDoc}
     */
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): DefinitionLocation
    {
        foreach ($this->finder->findReferences($document, $byteOffset) as $reference) {
            assert($reference instanceof PotentialLocation);
            return new DefinitionLocation($reference->location()->uri(), $reference->location()->offset());
        }

        throw new CouldNotLocateDefinition('Could not locate any references to variable');
    }
}
