<?php

namespace Phpactor\CodeTransform\Domain;

use Amp\Promise;
use Phpactor\TextDocument\TextEdits;

interface Transformer
{
    /**
     * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise;

    /**
     * Return the issues that this transform will fix.
     * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise;
}
