<?php

namespace Phpactor\Completion\Core\DocumentPrioritizer;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\TextDocumentUri;

class DefaultResultPrioritizer implements DocumentPrioritizer
{
    public function __construct(private readonly int $priority = Suggestion::PRIORITY_LOW)
    {
    }

    public function priority(?TextDocumentUri $one, ?TextDocumentUri $two): int
    {
        return $this->priority;
    }
}
