<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TolerantArrayCompletor implements TolerantCompletor
{
    /**
     * @param Suggestion[] $suggestions
     */
    public function __construct(private readonly array $suggestions)
    {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        yield from $this->suggestions;

        return true;
    }
}
