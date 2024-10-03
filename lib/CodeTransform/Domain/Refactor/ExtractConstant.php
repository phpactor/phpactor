<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;

interface ExtractConstant
{
    public function extractConstant(SourceCode $sourceCode, int $offset, string $constantName): TextDocumentEdits;

    public function canExtractConstant(SourceCode $source, int $offset): bool;
}
