<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface FillObject
{
    public function fillObject(TextDocument $document, ByteOffset $offset): TextEdits;
}
