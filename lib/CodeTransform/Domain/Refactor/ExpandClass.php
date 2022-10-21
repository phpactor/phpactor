<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;

interface ExpandClass
{
    public function getTextEdits(SourceCode $sourceCode, int $offset): TextDocumentEdits;

    public function canExpandClassName(SourceCode $sourceCode, int $offset): bool;
}
