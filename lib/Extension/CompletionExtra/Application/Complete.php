<?php

namespace Phpactor\Extension\CompletionExtra\Application;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class Complete
{
    public function __construct(private TypedCompletorRegistry $registry)
    {
    }

    /**
     * @return array{
     *    suggestions: array<array<string,mixed>>,
     *    issues: array
     * }
     */
    public function complete(string $source, int $offset, string $type = 'php'): array
    {
        $completor = $this->registry->completorForType($type);
        $suggestions = $completor->complete(
            TextDocumentBuilder::create($source)->language($type)->build(),
            ByteOffset::fromInt($offset)
        );
        $suggestions = iterator_to_array($suggestions);
        $suggestions = array_map(function (Suggestion $suggestion) {
            return $suggestion->toArray();
        }, $suggestions);

        return [
            'suggestions' => $suggestions,

            // deprecated
            'issues' => [],
        ];
    }
}
