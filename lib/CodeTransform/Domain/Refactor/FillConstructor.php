<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface FillConstructor
{
    public function fillConstructor(TextDocument $document, ByteOffset $offset): TextEdits;
}
