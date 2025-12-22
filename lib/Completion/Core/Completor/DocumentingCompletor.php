<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\SuggestionDocumentor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DocumentingCompletor implements Completor
{
    public function __construct(
        private Completor $innerCompletor,
        private SuggestionDocumentor $documentor
    ) {
    }

    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $suggestions = $this->innerCompletor->complete($source, $byteOffset);
        foreach ($suggestions as $suggestion) {
            if (false === $suggestion->hasDocumentation()) {
                $suggestion = $suggestion->withDocumentation($this->documentor->document($suggestion));
            }
            yield $suggestion;
        }
        return $suggestions->getReturn();
    }
}
