<?php

namespace Phpactor\Completion\Core\DocumentPrioritizer;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\TextDocumentUri;

class DefaultResultPrioritizer implements DocumentPrioritizer
{
    private int $priority;

    public function __construct(int $priority = Suggestion::PRIORITY_LOW)
    {
        $this->priority = $priority;
    }

    public function priority(?TextDocumentUri $one, ?TextDocumentUri $two): int
    {
        return $this->priority;
    }
}
