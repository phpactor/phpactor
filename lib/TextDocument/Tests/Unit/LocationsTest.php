<?php

namespace Phpactor\TextDocument\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use RuntimeException;

class LocationsTest extends TestCase
{
    public function testContainsLocations(): void
    {
        $locations = new Locations([
            Location::fromPathAndOffset('/path/to.php', 12),
            Location::fromPathAndOffset('/path/to.php', 13)
        ]);
        $this->assertCount(2, $locations);
    }

    public function testIsCountable(): void
    {
        $locations = new Locations([
            Location::fromPathAndOffset('/path/to.php', 12),
            Location::fromPathAndOffset('/path/to.php', 13)
        ]);
        $this->assertEquals(2, $locations->count());
    }

    public function testExceptionIfFirstNotAvailable(): void
    {
        $this->expectException(RuntimeException::class);
        $locations = new Locations([
        ]);
        $locations->first();
    }

    public function testAppendLocations(): void
    {
        $locations = new Locations([
            Location::fromPathAndOffset('/path/to.php', 12),
        ]);
        $locations = $locations->append(new Locations([
            Location::fromPathAndOffset('/path/to.php', 13),
        ]));

        self::assertEquals(new Locations([
            Location::fromPathAndOffset('/path/to.php', 12),
            Location::fromPathAndOffset('/path/to.php', 13)
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
        $this->assertEquals($sortedLocationsArray, iterator_to_array($sortedLocations));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideUnsortedLocations(): Generator
    {
        yield '2 files and 3 references' => [[
            Location::fromPathAndOffset('/path/to.php', 15),
            Location::fromPathAndOffset('/path/to.php', 12),
            Location::fromPathAndOffset('/path/from.php', 13),
        ], [
            Location::fromPathAndOffset('/path/from.php', 13),
            Location::fromPathAndOffset('/path/to.php', 12),
            Location::fromPathAndOffset('/path/to.php', 15),
        ]];
    }
}
