<?php

namespace Phpactor\Extension\LanguageServerIndexer\Status;

use Phpactor\AmpFsWatch\Watcher;
use Phpactor\Extension\LanguageServer\Status\StatusProvider;

class IndexerStatusProvider implements StatusProvider
{
    private Watcher $watcher;

    public function __construct(Watcher $watcher)
    {
        $this->watcher = $watcher;
    }

    public function title(): string
    {
        return 'Indexer';
    }

    public function provide(): array
    {
        return [
            'watcher' => $this->watcher->describe(),
        ];
    }
}
