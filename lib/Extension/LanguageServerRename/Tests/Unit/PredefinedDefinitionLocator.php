<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit;

use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class PredefinedDefinitionLocator implements DefinitionLocator
{
    /**
     * @var DefinitionLocation|null
     */
    private $location;

    public function __construct(?DefinitionLocation $locationr)
    {
        $this->location = $locationr;
    }

    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): DefinitionLocation
    {
        if ($this->location !== null) {
            return $this->location;
        }

        throw new CouldNotLocateDefinition();
    }
}
