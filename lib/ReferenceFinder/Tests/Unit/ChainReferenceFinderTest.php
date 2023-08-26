<?php


namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainReferenceFinder;
use Phpactor\ReferenceFinder\ClassReferenceFinder;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainReferenceFinderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<ClassReferenceFinder>
     */
    private ObjectProphecy $locator1;

    /**
     * @var ObjectProphecy<ClassReferenceFinder>
     */
    private ObjectProphecy $locator2;

    private TextDocument $document;

    private ByteOffset $offset;

    public function setUp(): void
    {
        $this->locator1 = $this->prophesize(ReferenceFinder::class);
        $this->locator2 = $this->prophesize(ReferenceFinder::class);

        $this->document = TextDocumentBuilder::create('<?php ')->build();
        $this->offset = ByteOffset::fromInt(1234);
    }

    public function testProvidesAggregateLocations(): void
    {
        $locator = new ChainReferenceFinder([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ]);

        $location1 = $this->createLocation();
        $this->locator1->findReferences($this->document, $this->offset)->willYield([$location1]);

        $location2 = $this->createLocation();
        $this->locator2->findReferences($this->document, $this->offset)->willYield([$location2]);

        $locations = [];
        foreach ($locator->findReferences($this->document, $this->offset) as $location) {
            $locations[] = $location;
        }

        $this->assertEquals([$location1, $location2], $locations);
    }

    private function createLocation(): Location
    {
        return Location::fromPathAndOffsets('/path/to.php', 1234, 4578);
    }
}
