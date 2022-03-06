<?php

namespace Phpactor\Completion\Core;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ChainCompletor implements Completor
{
    /**
     * @var Completor[]
     */
    private $completors;

    /**
     * @param Completor[] $completors
     */
    public function __construct(array $completors)
    {
        $this->completors = $completors;
    }

    public function complete(TextDocument $source, ByteOffset $offset): Generator
    {
        $isComplete = true;

        foreach ($this->completors as $completor) {
            $suggestions = $completor->complete($source, $offset);

            yield from $suggestions;

            $isComplete = $isComplete && $suggestions->getReturn();
        }

        return $isComplete;
    }
}
