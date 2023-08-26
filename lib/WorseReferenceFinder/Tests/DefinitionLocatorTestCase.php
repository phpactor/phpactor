<?php

namespace Phpactor\WorseReferenceFinder\Tests;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Tests\Unit\LocationAssertions;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class DefinitionLocatorTestCase extends IntegrationTestCase
{
    use LocationAssertions;

    protected function assertTypeLocation(TypeLocation $typeLocation, string $path, int $start, int $end): void
    {
        self::assertLocation(
            $typeLocation->location(),
            $this->workspace->path($path),
            $start,
            $end
        );
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
