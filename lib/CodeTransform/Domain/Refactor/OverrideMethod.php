<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;

interface OverrideMethod
{
    public function overrideMethod(SourceCode $source, string $className, string $methodName): string;
}
