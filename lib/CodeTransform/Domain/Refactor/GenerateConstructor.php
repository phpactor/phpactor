<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\WorkspaceEdits;

interface GenerateConstructor
{
    public function generateMethod(TextDocument $document, ByteOffset $offset): WorkspaceEdits;
}
