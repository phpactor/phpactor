<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;

interface OverrideMethod
{
    public function overrideMethod(SourceCode $source, string $className, string $methodName): TextEdits;
}
