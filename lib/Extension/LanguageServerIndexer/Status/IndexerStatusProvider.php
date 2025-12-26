<?php

namespace Phpactor\Extension\LanguageServerIndexer\Status;

use Phpactor\AmpFsWatch\Watcher;
use Phpactor\Extension\LanguageServer\Status\StatusProvider;

class IndexerStatusProvider implements StatusProvider
{
    public function __construct(private readonly Watcher $watcher)
    {
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
