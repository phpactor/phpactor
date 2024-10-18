<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;

interface ExtractExpression
{
    public function canExtractExpression(SourceCode $source, int $offsetStart, ?int $offsetEnd): bool;

    public function extractExpression(SourceCode $source, int $offsetStart, ?int $offsetEnd, string $variableName): TextEdits;
}
