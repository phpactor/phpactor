<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Location;

class LocationTest extends TestCase
{
    public function testProvidesAccessToUri(): void
    {
        $location = Location::fromPathAndOffsets('/path/to.php', 123, 234);
        $this->assertEquals('file:///path/to.php', $location->uri()->__toString());
    }

    public function testProvidesAccessToByteOffset(): void
    {
        $location = Location::fromPathAndOffsets('/path/to.php', 123, 455);
        $this->assertEquals(123, $location->range()->start()->toInt());
        $this->assertEquals(455, $location->range()->end()->toInt());
    }
}
