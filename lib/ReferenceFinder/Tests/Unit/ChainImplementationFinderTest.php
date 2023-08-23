<?php


namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainImplementationFinder;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainImplementationFinderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<ClassImplementationFinder>
     */
    private ObjectProphecy $locator1;

    /**
     * @var ObjectProphecy<ClassImplementationFinder>
     */
    private ObjectProphecy $locator2;

    private TextDocument $document;

    private ByteOffset $offset;

    public function setUp(): void
    {
        $this->locator1 = $this->prophesize(ClassImplementationFinder::class);
        $this->locator2 = $this->prophesize(ClassImplementationFinder::class);

        $this->document = TextDocumentBuilder::create('<?php ')->build();
        $this->offset = ByteOffset::fromInt(1234);
    }

    public function testProvidesAggregateLocations(): void
    {
        $locator = new ChainImplementationFinder([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ]);

        $location1 = $this->createLocation();
        $this->locator1->findImplementations($this->document, $this->offset, false)->willReturn(new LocationRanges([$location1]));

        $location2 = $this->createLocation();
        $this->locator2->findImplementations($this->document, $this->offset, false)->willReturn(new LocationRanges([$location2]));

        $locationRanges = $locator->findImplementations($this->document, $this->offset);
        $this->assertEquals(new LocationRanges([$location1, $location2]), $locationRanges);
    }

    private function createLocation(): LocationRange
    {
        return LocationRange::fromPathAndOffsets('/path/to.php', 1234, 1234);
    }
}
