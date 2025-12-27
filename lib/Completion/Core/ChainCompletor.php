<?php

namespace Phpactor\Completion\Core;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ChainCompletor implements Completor
{
    /**
     * @param Completor[] $completors
     */
    public function __construct(
        private array $completors,
        private CompletorLogger $logger = new CompletorLogger(),
    ) {
    }

    public function complete(TextDocument $source, ByteOffset $offset): Generator
    {
        $isComplete = true;

        foreach ($this->completors as $completor) {
            $start = microtime(true);
            $suggestions = $completor->complete($source, $offset);

            yield from $suggestions;

            $this->logger->timeTaken($completor, microtime(true) - $start);
            $isComplete = $isComplete && $suggestions->getReturn();
        }

        return $isComplete;
    }
}
