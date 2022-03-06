<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;

interface GenerateMethod
{
    public function generateMethod(SourceCode $sourceCode, int $offset): TextDocumentEdits;
}
