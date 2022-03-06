<?php

namespace Phpactor\Indexer\Model;

use Phpactor\TextDocument\TextDocumentUri;

interface DirtyDocumentTracker
{
    public function markDirty(TextDocumentUri $uri): void;
}
