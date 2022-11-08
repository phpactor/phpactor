<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

interface ReplaceQualifierWithImport
{
    public function getTextEdits(TextDocumentItem $document, int $offset): TextDocumentEdits;

    public function canReplaceWithImport(SourceCode $sourceCode, int $offset): bool;
}
