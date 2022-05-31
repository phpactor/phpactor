<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Diagnostics;

interface DiagnosticReflector
{
    /**
     * @param TextDocument|string $sourceCode
     */
    public function diagnostics($sourceCode): Diagnostics;
}
