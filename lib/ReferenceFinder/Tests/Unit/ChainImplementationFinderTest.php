<?php


namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ChainImplementationFinder;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainImplementationFinderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|ClassImplementationFinder
     */
    private $locator1;

    /**
     * @var ObjectProphecy|ClassImplementationFinder
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
        $this->locator1->findImplementations($this->document, $this->offset)->willReturn(new Locations([$location1]));

        $location2 = $this->createLocation();
        $this->locator2->findImplementations($this->document, $this->offset)->willReturn(new Locations([$location2]));

        $locations = $locator->findImplementations($this->document, $this->offset);
        $this->assertEquals(new Locations([$location1, $location2], $locations), $locations);
    }

    private function createLocation(): Location
    {
        return new Location(TextDocumentUri::fromString('/path/to.php'), ByteOffset::fromInt(1234));
    }
}
