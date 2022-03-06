<?php

namespace Phpactor\Indexer\Model\DirtyDocumentTracker;

use Phpactor\Indexer\Model\DirtyDocumentTracker;
use Phpactor\TextDocument\TextDocumentUri;

class NullDirtyDocumentTracker implements DirtyDocumentTracker
{
    public function markDirty(TextDocumentUri $uri): void
    {
    }
}
