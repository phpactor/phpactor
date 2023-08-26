<?php

namespace Phpactor\TextDocument\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use RuntimeException;

class LocationsTest extends TestCase
{
    use LocationAssertions;

    public function testContainsLocations(): void
    {
        $locations = new Locations([
            Location::fromPathAndOffsets('/path/to.php', 12, 124),
            Location::fromPathAndOffsets('/path/to.php', 13, 14)
        ]);
        $this->assertCount(2, $locations);
    }

    public function testIsCountable(): void
    {
        $locations = new Locations([
            Location::fromPathAndOffsets('/path/to.php', 12, 12),
            Location::fromPathAndOffsets('/path/to.php', 13, 13)
        ]);

        $this->assertEquals(2, $locations->count());
    }

    public function testExceptionIfFirstNotAvailable(): void
    {
        $this->expectException(RuntimeException::class);

        $locations = new Locations([]);
        $locations->first();
    }

    public function testAppendLocations(): void
    {
        $locations = new Locations([
            Location::fromPathAndOffsets('/path/to.php', 12, 19),
        ]);
        $locations = $locations->append(new Locations([
            Location::fromPathAndOffsets('/path/to.php', 13, 40),
        ]));

        self::assertEquals(new Locations([
            Location::fromPathAndOffsets('/path/to.php', 12, 19),
            Location::fromPathAndOffsets('/path/to.php', 13, 40)
        ]), $locations);
    }

    /**
     * @dataProvider provideUnsortedLocations
     *
     * @param Location[] $unsortedLocationsArray
     * @param Location[] $sortedLocationsArray
     */
    public function testSortLocations(
        array $unsortedLocationsArray,
        array $sortedLocationsArray
    ): void {
        $locations = new Locations($unsortedLocationsArray);
        $sortedLocations = $locations->sorted();

        $this->assertNotSame($locations, $sortedLocations);
        $this->assertSame(count($unsortedLocationsArray), count($sortedLocations));

        foreach(iterator_to_array($sortedLocations) as $index => $sortedLocation) {
            $expectedLocation = $sortedLocationsArray[$index];
            self::assertLocation($sortedLocation, $expectedLocation->uri()->path(), $expectedLocation->range()->start()->toInt(), $expectedLocation->range()->end()->toInt());
        }
    }

    /**
     * @return Generator<mixed>
     */
    public function provideUnsortedLocations(): Generator
    {
        yield 'Same file is sorted by start position' => [[
            Location::fromPathAndOffsets('/path/to.php', 30, 50),
            Location::fromPathAndOffsets('/path/to.php', 12, 24),
        ], [
            Location::fromPathAndOffsets('/path/to.php', 12, 24),
            Location::fromPathAndOffsets('/path/to.php', 30, 50),
        ]];

        yield 'Sort by file name first' => [[
            Location::fromPathAndOffsets('/path/to.php', 12, 42),
            Location::fromPathAndOffsets('/path/from.php', 15, 43),
        ], [
            Location::fromPathAndOffsets('/path/from.php', 15, 43),
            Location::fromPathAndOffsets('/path/to.php', 12, 42),
        ]];
    }
}
