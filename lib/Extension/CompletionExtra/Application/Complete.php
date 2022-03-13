<?php

namespace Phpactor\Extension\CompletionExtra\Application;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class Complete
{
    private TypedCompletorRegistry $registry;

    public function __construct(TypedCompletorRegistry $registry)
    {
        $this->registry = $registry;
    }

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
