<?php

namespace Phpactor\CodeTransform\Domain\Helper;

use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\TextDocument\TextDocument;

/**
 * @deprecated Use the UnresolvableNameDiagnostic instead.
 */
interface UnresolvableClassNameFinder
{
    public function find(TextDocument $sourceCode): NameWithByteOffsets;
}
