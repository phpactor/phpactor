<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

final class ChainReferenceFinder implements ReferenceFinder
{
    /**
     * @var ReferenceFinder[]
     */
    private array $finders = [];

    public function __construct(array $finders)
    {
        foreach ($finders as $finder) {
            $this->add($finder);
        }
    }

    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        foreach ($this->finders as $finder) {
            $generator = $finder->findReferences($document, $byteOffset);
            yield from $generator;

            // stop no more generators should be executed
            if ($generator->getReturn() === true) {
                return true;
            }
        }

        return false;
    }

    private function add(ReferenceFinder $finder): void
    {
        $this->finders[] = $finder;
    }
}
