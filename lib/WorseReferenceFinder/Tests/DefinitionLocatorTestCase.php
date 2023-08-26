<?php

namespace Phpactor\WorseReferenceFinder\Tests;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class DefinitionLocatorTestCase extends IntegrationTestCase
{
    protected function assertTypeLocation(TypeLocation $typeLocation, string $path, int $start, int $end): void
    {
        $location = $typeLocation->location();

        $this->assertEquals($this->workspace->path($path), $location->uri()->path());
        $this->assertEquals($start, $location->range()->start()->toInt(), 'Start position does not match.');
        $this->assertEquals($end, $location->range()->end()->toInt(), 'End position does not match.');
    }


    protected function locate(string $manifset, string $source): TypeLocations
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $documentUri = $this->workspace->path('somefile.php');
        $this->workspace->loadManifest($manifset);
        return $this->locator()->locateDefinition(
            TextDocumentBuilder::create($source)->uri($documentUri)->language('php')->build(),
            ByteOffset::fromInt((int)$offset)
        );
    }

    abstract protected function locator(): DefinitionLocator;
}
