<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;

interface ReplaceQualifierWithImport
{
    public function getTextEdits(SourceCode $sourceCode, int $offset): TextDocumentEdits;

    public function canReplaceWithImport(SourceCode $sourceCode, int $offset): bool;
}
