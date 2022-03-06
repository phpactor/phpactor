<?php

namespace Phpactor\WorseReferenceFinder\Tests;

use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class DefinitionLocatorTestCase extends IntegrationTestCase
{
    protected function locate(string $manifset, string $source): DefinitionLocation
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $documentUri = $this->workspace->path('somefile.php');
        $this->workspace->loadManifest($manifset);
        return $this->locator()->locateDefinition(
            TextDocumentBuilder::create($source)->uri($documentUri)->language('php')->build(),
            ByteOffset::fromInt($offset)
        );
    }

    abstract protected function locator(): DefinitionLocator;
}
