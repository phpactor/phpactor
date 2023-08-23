<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\DefinitionAndReferenceFinder;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\TestDefinitionLocator;
use Phpactor\ReferenceFinder\TestReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\TypeFactory;
use function iterator_to_array;

class DefinitionAndReferenceFinderTest extends TestCase
{
    public function testReturnsBothDefinitionAndReference(): void
    {
        $finder = new DefinitionAndReferenceFinder(
            TestDefinitionLocator::fromSingleLocation(
                TypeFactory::unknown(),
                LocationRange::fromPathAndOffsets('/path', 1, 1)
            ),
            new TestReferenceFinder(PotentialLocation::surely(LocationRange::fromPathAndOffsets('/path', 2, 2)))
        );
        $document = TextDocumentBuilder::create('asd')->build();
        self::assertCount(2, iterator_to_array($finder->findReferences($document, ByteOffset::fromInt(1))));
    }

    public function testReturnsReferenceIfDefinitionNotFound(): void
    {
        $finder = new DefinitionAndReferenceFinder(
            new TestDefinitionLocator(null),
            new TestReferenceFinder(PotentialLocation::surely(LocationRange::fromPathAndOffsets('/path', 2, 2)))
        );
        $document = TextDocumentBuilder::create('asd')->build();
        self::assertCount(1, iterator_to_array($finder->findReferences($document, ByteOffset::fromInt(1))));
    }
}
