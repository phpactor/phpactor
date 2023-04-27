<?php

namespace Phpactor\Extension\Laravel\ReferenceFinder;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\Util\WordAtOffset;
use Phpactor\WorseReflection\Core\Type\StringType;

class ViewReferenceFinder implements DefinitionLocator
{
    public function __construct(
        private LaravelContainerInspector $container,
    ) {
    }


    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        if (!$document->language()->in(['php', 'blade'])) {
            throw new UnsupportedDocument(sprintf('Must be php or blade got "%s"', $document->language()));
        }

        $stringLiteralWord = trim((new WordAtOffset())($document->__toString(), $byteOffset->toInt()), '\'",');

        $view = $this->container->views()[$stringLiteralWord] ?? false;

        if (!$view) {
            throw new UnsupportedDocument('Unsupported view');
        }

        return new TypeLocations([
            new TypeLocation(
                new StringType(),
                new Location(TextDocumentUri::fromString($view), ByteOffset::fromInt(0))
            )
        ]);
    }
}
