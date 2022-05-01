<?php

namespace Phpactor\Rename\Tests\Adapter\ReferenceFinder;

use PHPUnit\Framework\TestCase;
use Phpactor\Rename\Model;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;

abstract class ReferenceRenamerIntegrationTestCase extends TestCase
{
    /**
     * @return ByteOffset[]
     */
    public function offsetsToReferenceFinder(TextDocument $textDocument, array $references): Model
    {
        return new Model(...array_map(function (ByteOffset $reference) use ($textDocument) {
            return PotentialLocation::surely(new Location($textDocument->uri(), $reference));
        }, $references));
    }
}
