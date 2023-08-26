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
        $locations = new Locations([
        ]);
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
            Location::fromPathAndOffsets('/path/to.php', 12, 89),
            Location::fromPathAndOffsets('/path/to.php', 13, 18)
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
            Location::fromPathAndOffsets('/path/to.php', 15, 20),
            Location::fromPathAndOffsets('/path/to.php', 12, 42),
            Location::fromPathAndOffsets('/path/from.php', 13, 43),
        ], [
            Location::fromPathAndOffsets('/path/from.php', 13, 22),
            Location::fromPathAndOffsets('/path/to.php', 12, 45),
            Location::fromPathAndOffsets('/path/to.php', 15, 44),
        ]];
    }
}
