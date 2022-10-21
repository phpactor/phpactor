<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;

interface SimplifyClassName
{
    public function getTextEdits(SourceCode $sourceCode, int $offset): TextDocumentEdits;

    public function canSimplifyClassName(SourceCode $sourceCode, int $offset): bool;
}
