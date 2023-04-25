<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentUri;

class LocationTest extends TestCase
{
    const EXAMPLE_URI = '/path/to.php';
    const EXAMPLE_OFFSET = 1234;

    public function testValues(): void
    {
        $location = new Location(
            TextDocumentUri::fromString(self::EXAMPLE_URI),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertEquals(self::EXAMPLE_URI, $location->uri()->path());
        $this->assertEquals(self::EXAMPLE_OFFSET, $location->offset()->toInt());
    }
}
