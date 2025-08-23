<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

interface ReferenceFinder
{
    /**
     * Find references to the symbol at the given byte offset.
     *
     * Return true if no more finders should be executed after this finder and false otherwise.
     *
     * @return Generator<int, PotentialLocation, null, bool>
     */
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator;
}
