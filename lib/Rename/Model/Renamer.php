<?php

namespace Phpactor\Rename\Model;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

interface Renamer
{
    public function getRenameRange(TextDocument $textDocument, ByteOffset $offset): ?ByteOffsetRange;

    /**
     * @return Generator<LocatedTextEdit>
     */
    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator;
}
