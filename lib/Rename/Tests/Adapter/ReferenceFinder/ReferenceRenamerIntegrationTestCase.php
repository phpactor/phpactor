<?php

namespace Phpactor\Rename\Tests\Adapter\ReferenceFinder;

use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\Rename\Model\ReferenceFinder\PredefinedReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use PHPUnit\Framework\TestCase;

abstract class ReferenceRenamerIntegrationTestCase extends TestCase
{
    /**
     * @param ByteOffset[] $references
     */
    public function offsetsToReferenceFinder(TextDocument $textDocument, array $references): ReferenceFinder
    {
        return new PredefinedReferenceFinder(...array_map(function (ByteOffset $reference) use ($textDocument) {
            return PotentialLocation::surely(new Location($textDocument->uri(), $reference));
        }, $references));
    }
}
