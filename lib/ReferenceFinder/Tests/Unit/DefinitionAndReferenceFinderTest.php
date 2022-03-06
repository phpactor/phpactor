<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\DefinitionAndReferenceFinder;
use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\TestDefinitionLocator;
use Phpactor\ReferenceFinder\TestReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use function iterator_to_array;

class DefinitionAndReferenceFinderTest extends TestCase
{
    public function testReturnsBothDefinitionAndReference(): void
    {
        $finder = new DefinitionAndReferenceFinder(
            new TestDefinitionLocator(new DefinitionLocation(TextDocumentUri::fromString('/path'), ByteOffset::fromInt(1))),
            new TestReferenceFinder(PotentialLocation::surely(Location::fromPathAndOffset('/path', 2)))
        );
        $document = TextDocumentBuilder::create('asd')->build();
        self::assertCount(2, iterator_to_array($finder->findReferences($document, ByteOffset::fromInt(1))));
    }

    public function testReturnsReferenceIfDefinitionNotFound(): void
    {
        $finder = new DefinitionAndReferenceFinder(
            new TestDefinitionLocator(null),
            new TestReferenceFinder(PotentialLocation::surely(Location::fromPathAndOffset('/path', 2)))
        );
        $document = TextDocumentBuilder::create('asd')->build();
        self::assertCount(1, iterator_to_array($finder->findReferences($document, ByteOffset::fromInt(1))));
    }
}
