<?php

namespace Phpactor\Completion\Core;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ChainCompletor implements Completor
{
    /**
     * @param Completor[] $completors
     */
    public function __construct(
        private array $completors,
        private LoggerInterface $logger = new NullLogger(
        )
    ) {
    }

    public function complete(TextDocument $source, ByteOffset $offset): Generator
    {
        $isComplete = true;

        foreach ($this->completors as $completor) {
            $start = microtime(true);
            $suggestions = $completor->complete($source, $offset);

            yield from $suggestions;

            $this->logger->info(sprintf(
                'COMP %s %s',
                number_format(microtime(true) - $start, 4),
                $completor::class,
            ));
            $isComplete = $isComplete && $suggestions->getReturn();
        }

        return $isComplete;
    }
}
