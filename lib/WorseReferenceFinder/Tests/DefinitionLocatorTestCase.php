<?php

namespace Phpactor\WorseReferenceFinder\Tests;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class DefinitionLocatorTestCase extends IntegrationTestCase
{
    protected function assertTypeLocation(TypeLocation $typeLocation, string $path, int $start, int $end): void
    {
        self::assertEquals(
            $typeLocation->location(),
            Location::fromPathAndOffsets($this->workspace->path($path), $start, $end)
        );
    }


    protected function locate(string $manifest, string $source): TypeLocations
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $documentUri = $this->workspace->path('somefile.php');
        $this->workspace->loadManifest($manifest);
        return $this->locator()->locateDefinition(
            TextDocumentBuilder::create($source)->uri($documentUri)->language('php')->build(),
            ByteOffset::fromInt((int)$offset)
        );
    }

    abstract protected function locator(): DefinitionLocator;
}
