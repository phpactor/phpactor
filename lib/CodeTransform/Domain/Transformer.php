<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\TextDocument\TextEdits;

interface Transformer
{
    public function transform(SourceCode $code): TextEdits;

    /**
     * Return the issues that this transform will fix.
     */
    public function diagnostics(SourceCode $code): Diagnostics;
}
