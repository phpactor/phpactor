<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainTypeLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainTypeLocatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<TypeLocator>
     */
    private ObjectProphecy $locator1;

    /**
     * @var ObjectProphecy<TypeLocator>
     */
    private ObjectProphecy $locator2;

    private TextDocument $document;

    private ByteOffset $offset;

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
        $this->locator1->locateTypes($this->document, $this->offset)->willReturn($this->createLocations($location1));

        $location2 = $this->createLocation();
        $this->locator2->locateTypes($this->document, $this->offset)->willReturn($this->createLocations($location2));

        $location = $locator->locateTypes($this->document, $this->offset);
        $this->assertSame($location->first()->range(), $location1);
    }

    public function testExceptionWhenTypeNotFound(): void
    {
        $this->expectException(CouldNotLocateType::class);
        $this->expectExceptionMessage('No');

        $locator = new ChainTypeLocator([
            $this->locator1->reveal()
        ]);

        $this->locator1->locateTypes($this->document, $this->offset)->willThrow(new CouldNotLocateType('No'));
        $locator->locateTypes($this->document, $this->offset);
    }

    public function testExceptionWhenTypeNotSupported(): void
    {
        $this->expectException(CouldNotLocateType::class);
        $this->expectExceptionMessage('Not supported');

        $locator = new ChainTypeLocator([
            $this->locator1->reveal()
        ]);

        $this->locator1->locateTypes($this->document, $this->offset)->willThrow(new UnsupportedDocument('Not supported'));
        $locator->locateTypes($this->document, $this->offset);
    }

    private function createLocation(): LocationRange
    {
        return new LocationRange(
            TextDocumentUri::fromString('/path/to.php'),
            ByteOffsetRange::fromByteOffsets(ByteOffset::fromInt(1234), ByteOffset::fromInt(1234))
        );
    }

    private function createLocations(LocationRange $location1): TypeLocations
    {
        return new TypeLocations([
            new TypeLocation(new MixedType(), $location1)
        ]);
    }
}
