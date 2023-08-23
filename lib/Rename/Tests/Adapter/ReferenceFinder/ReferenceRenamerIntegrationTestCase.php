<?php

namespace Phpactor\Rename\Tests\Adapter\ReferenceFinder;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\Rename\Model\ReferenceFinder\PredefinedReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;

abstract class ReferenceRenamerIntegrationTestCase extends TestCase
{
    /**
     * @param ByteOffset[] $references
     */
    public function offsetsToReferenceFinder(TextDocument $textDocument, array $references): ReferenceFinder
    {
        return new PredefinedReferenceFinder(...array_map(function (ByteOffset $reference) use ($textDocument) {
            return PotentialLocation::surely(
                new LocationRange($textDocument->uriOrThrow(), ByteOffsetRange::fromByteOffsets($reference, $reference))
            );
        }, $references));
    }
}
