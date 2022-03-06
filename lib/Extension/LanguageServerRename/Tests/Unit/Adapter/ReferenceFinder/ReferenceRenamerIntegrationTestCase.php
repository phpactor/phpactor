<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit\Adapter\ReferenceFinder;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerRename\Tests\Unit\PredefinedReferenceFinder;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;

abstract class ReferenceRenamerIntegrationTestCase extends TestCase
{
    /**
     * @return ByteOffset[]
     */
    public function offsetsToReferenceFinder(TextDocument $textDocument, array $references): PredefinedReferenceFinder
    {
        return new PredefinedReferenceFinder(...array_map(function (ByteOffset $reference) use ($textDocument) {
            return PotentialLocation::surely(new Location($textDocument->uri(), $reference));
        }, $references));
    }
}
