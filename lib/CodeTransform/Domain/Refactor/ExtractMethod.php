<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;

interface ExtractMethod
{
    public function canExtractMethod(SourceCode $source, int $offsetStart, int $offsetEnd): bool;

    public function extractMethod(SourceCode $source, int $offsetStart, int $offsetEnd, string $name): TextDocumentEdits;
}
