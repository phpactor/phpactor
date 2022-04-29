<?php

namespace Phpactor\WorseReferenceFinder;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use function assert;

class TolerantVariableDefintionLocator implements DefinitionLocator
{
    private TolerantVariableReferenceFinder $finder;

    public function __construct(TolerantVariableReferenceFinder $finder)
    {
        $this->finder = $finder;
    }

    
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        foreach ($this->finder->findReferences($document, $byteOffset) as $reference) {
            assert($reference instanceof PotentialLocation);
            return TypeLocations::forLocation(new TypeLocation(
                // we don't have the type info of the variable here, but
                // there'll only be one so we don't need it.
                TypeFactory::undefined(),
                new Location($reference->location()->uri(), $reference->location()->offset())
            ));
        }

        throw new CouldNotLocateDefinition('Could not locate any references to variable');
    }
}
