<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface PromoteProperty
{
    public function promoteProperty(TextDocument $document, ByteOffset $offset): TextEdits;
}
