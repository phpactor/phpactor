<?php

namespace Phpactor\Completion\Core;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

interface NameSuggestionProvider
{
    /**
     * @return Generator<Suggestion>
     */
    public function provide(TextDocument $textDocument, ByteOffset $offset, string $search): Generator;
}
