<?php

namespace Phpactor\Extension\Laravel\ReferenceFinder;

use Phpactor\Extension\Behat\Behat\Step;
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
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Reflector;

class ViewReferenceFinder implements DefinitionLocator
{
    public function __construct(
        private LaravelContainerInspector $container,
        private Reflector $reflector,
    ) {
    }


    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        if (!$document->language()->in(['php', 'blade'])) {
            throw new UnsupportedDocument(sprintf('Language must be one of cucumber, behat or gherkin, got "%s"', $document->language()));
        }

        $stringLiteralWord = trim((new WordAtOffset())($document->__toString(), $byteOffset->toInt()), '\'"');

        $viewUsage = $this->container->viewsData()['viewUsageMapping'][$stringLiteralWord] ?? [];

        if ($viewUsage === []) {
            $viewUsage = [$this->container->views()[$stringLiteralWord]];
        }

        return new TypeLocations(array_map(function (string $path) {
            return new TypeLocation(
                new StringType(),
                new Location(TextDocumentUri::fromString($path), ByteOffset::fromInt(0))
            );
        }, $viewUsage));
    }
}
