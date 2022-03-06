<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainDefinitionLocationProvider;
use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainDefinitionLocationProviderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|DefinitionLocator
     */
    private $locator1;

    /**
     * @var ObjectProphecy|DefinitionLocator
     */
    private $locator2;

    /**
     * @var TextDocument
     */
    private $document;

    /**
     * @var ByteOffset
     */
    private $offset;

    public function setUp(): void
    {
        $this->locator1 = $this->prophesize(DefinitionLocator::class);
        $this->locator2 = $this->prophesize(DefinitionLocator::class);

        $this->document = TextDocumentBuilder::create('<?php ')->build();
        $this->offset = ByteOffset::fromInt(1234);
    }

    public function testProvidesAggregatedLocations(): void
    {
        $locator = new ChainDefinitionLocationProvider([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ]);

        $location1 = $this->createLocation();
        $this->locator1->locateDefinition($this->document, $this->offset)->willReturn($location1);

        $location2 = $this->createLocation();
        $this->locator2->locateDefinition($this->document, $this->offset)->willReturn($location2);

        $location = $locator->locateDefinition($this->document, $this->offset);
        $this->assertSame($location, $location1);
    }

    public function testExceptionWhenDefinitionNotFound(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('No');

        $locator = new ChainDefinitionLocationProvider([
            $this->locator1->reveal()
        ]);

        $this->locator1->locateDefinition($this->document, $this->offset)->willThrow(new CouldNotLocateDefinition('No'));
        $locator->locateDefinition($this->document, $this->offset);
    }

    public function testExceptionWhenDefinitionNotSupported(): void
    {
        $this->expectException(CouldNotLocateDefinition::class);
        $this->expectExceptionMessage('Not supported');

        $locator = new ChainDefinitionLocationProvider([
            $this->locator1->reveal()
        ]);

        $this->locator1->locateDefinition($this->document, $this->offset)->willThrow(new UnsupportedDocument('Not supported'));
        $locator->locateDefinition($this->document, $this->offset);
    }


    private function createLocation()
    {
        return new DefinitionLocation(TextDocumentUri::fromString('/path/to.php'), ByteOffset::fromInt(1234));
    }
}
