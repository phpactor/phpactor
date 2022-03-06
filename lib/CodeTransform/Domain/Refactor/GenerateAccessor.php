<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;

interface GenerateAccessor
{
    public function generate(SourceCode $sourceCode, string $propertyName, int $offset): SourceCode;
}
