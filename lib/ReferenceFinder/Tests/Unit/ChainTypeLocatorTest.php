<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainTypeLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainTypeLocatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|TypeLocator
     */
    private $locator1;

    /**
     * @var ObjectProphecy|TypeLocator
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
        $this->locator1 = $this->prophesize(TypeLocator::class);
        $this->locator2 = $this->prophesize(TypeLocator::class);

        $this->document = TextDocumentBuilder::create('<?php ')->build();
        $this->offset = ByteOffset::fromInt(1234);
    }

    public function testProvidesAggregatedLocations(): void
    {
        $locator = new ChainTypeLocator([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ]);

        $location1 = $this->createLocation();
        $this->locator1->locateType($this->document, $this->offset)->willReturn($location1);

        $location2 = $this->createLocation();
        $this->locator2->locateType($this->document, $this->offset)->willReturn($location2);

        $location = $locator->locateType($this->document, $this->offset);
        $this->assertSame($location, $location1);
    }

    public function testExceptionWhenTypeNotFound(): void
    {
        $this->expectException(CouldNotLocateType::class);
        $this->expectExceptionMessage('No');

        $locator = new ChainTypeLocator([
            $this->locator1->reveal()
        ]);

        $this->locator1->locateType($this->document, $this->offset)->willThrow(new CouldNotLocateType('No'));
        $locator->locateType($this->document, $this->offset);
    }

    public function testExceptionWhenTypeNotSupported(): void
    {
        $this->expectException(CouldNotLocateType::class);
        $this->expectExceptionMessage('Not supported');

        $locator = new ChainTypeLocator([
            $this->locator1->reveal()
        ]);

        $this->locator1->locateType($this->document, $this->offset)->willThrow(new UnsupportedDocument('Not supported'));
        $locator->locateType($this->document, $this->offset);
    }


    private function createLocation(): Location
    {
        return new Location(TextDocumentUri::fromString('/path/to.php'), ByteOffset::fromInt(1234));
    }
}
