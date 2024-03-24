<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface ByteOffsetRefactor
{
    public function refactor(TextDocument $document, ByteOffset $offset): TextEdits;
}
