<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;

interface ExtractConstant
{
    public function extractConstant(SourceCode $souceCode, int $offset, string $constantName): SourceCode;
}
