<?php

namespace Phpactor\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentUri;

class DefinitionLocationTest extends TestCase
{
    const EXAMPLE_URI = '/path/to.php';
    const EXAMPLE_OFFSET = 1234;

    public function testValues(): void
    {
        $location = new DefinitionLocation(
            TextDocumentUri::fromString(self::EXAMPLE_URI),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertEquals(self::EXAMPLE_URI, $location->uri()->path());
        $this->assertEquals(self::EXAMPLE_OFFSET, $location->offset()->toInt());
    }
}
