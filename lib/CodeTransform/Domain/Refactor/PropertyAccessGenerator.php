<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;

interface PropertyAccessGenerator
{
    /**
     * @param string[] $propertyNames
     */
    public function generate(SourceCode $sourceCode, array $propertyNames, int $offset): TextEdits;
}
