<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ArrayCompletor implements Completor
{
    private array $suggestions;

    /**
     * @param Suggestion[] $suggestions
     */
    public function __construct(array $suggestions)
    {
        $this->suggestions = $suggestions;
    }

    
    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        yield from $this->suggestions;

        return true;
    }
}
