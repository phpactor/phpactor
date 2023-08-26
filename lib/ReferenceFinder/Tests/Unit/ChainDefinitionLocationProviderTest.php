<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainDefinitionLocationProvider;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\TypeFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainDefinitionLocationProviderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<DefinitionLocator>
     */
    private ObjectProphecy $locator1;

    /**
     * @var ObjectProphecy<DefinitionLocator>
     */
    private ObjectProphecy $locator2;

    private TextDocument $document;

    private ByteOffset $offset;

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

    private function createLocation(): TypeLocations
    {
        return new TypeLocations([
            new TypeLocation(TypeFactory::unknown(), Location::fromPathAndOffsets('/path/to.php', 1234, 1234))
        ]);
    }
}
