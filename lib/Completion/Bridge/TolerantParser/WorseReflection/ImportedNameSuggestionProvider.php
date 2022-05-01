<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Phpactor\Completion\Core\NameSuggestionProvider;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ImportedNameSuggestionProvider implements NameSuggestionProvider
{
    public function provide(TextDocument $textDocument, ByteOffset $offset, string $search): Generator
    {
    }
}
