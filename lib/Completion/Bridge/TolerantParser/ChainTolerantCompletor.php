<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Phpactor\Completion\Core\CompletorLogger;
use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ChainTolerantCompletor implements Completor
{
    /**
     * @param TolerantCompletor[] $tolerantCompletors
     */
    public function __construct(
        private array $tolerantCompletors,
        private NodeAtCursorProvider $provider = new NodeAtCursorProvider(),
        private CompletorLogger $logger = new CompletorLogger(),
    ) {
    }

    /**
     * @return Generator<Suggestion>
     */
    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $node = $this->provider->get($source, $byteOffset);
        $isComplete = true;

        foreach ($this->tolerantCompletors as $tolerantCompletor) {
            $start = microtime(true);
            $completionNode = $node;

            if ($tolerantCompletor instanceof TolerantQualifiable) {
                $completionNode = $tolerantCompletor->qualifier()->couldComplete($node);
            }

            if (!$completionNode) {
                $this->logger->timeTaken($tolerantCompletor, microtime(true) - $start);
                continue;
            }

            $suggestions = $tolerantCompletor->complete($completionNode, $source, $byteOffset);

            yield from $suggestions;

            $this->logger->timeTaken($tolerantCompletor, microtime(true) - $start);

            $isComplete = $isComplete && $suggestions->getReturn();
        }

        return $isComplete;
    }

    private function filterNonQualifyingClasses(Node $node): array
    {
        return array_filter($this->tolerantCompletors, function (TolerantCompletor $completor) use ($node) {
            if (!$completor instanceof TolerantQualifiable) {
                return true;
            }

            return null !== $completor->qualifier()->couldComplete($node);
        });
    }
}
