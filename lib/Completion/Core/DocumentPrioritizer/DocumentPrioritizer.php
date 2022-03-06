<?php

namespace Phpactor\Completion\Core\DocumentPrioritizer;

use Phpactor\TextDocument\TextDocumentUri;

interface DocumentPrioritizer
{
    public function priority(?TextDocumentUri $one, ?TextDocumentUri $two): int;
}
