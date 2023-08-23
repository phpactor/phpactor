<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\TextDocument;

interface ClassImplementationFinder
{
    /**
     * Find implementations of the symbol under the given byte offset.
     *
     * If an interface FQN, then return location of classes which implement the
     * interface.
     *
     * If a call for method on an interface, then return location list of the class
     * implementations but with an offset position of the method.
     */
    public function findImplementations(TextDocument $document, ByteOffset $byteOffset, bool $includeDefinition = false): LocationRanges;
}
