<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Location;

class LocationTest extends TestCase
{
    public function testProvidesAccessToUri(): void
    {
        $location = Location::fromPathAndOffset('/path/to.php', 123);
        $this->assertEquals('file:///path/to.php', $location->uri()->__toString());
    }

    public function testProvidesAccessToByteOffset(): void
    {
        $location = Location::fromPathAndOffset('/path/to.php', 123);
        $this->assertEquals(123, $location->offset()->toInt());
    }
}
