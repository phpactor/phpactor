<?php

namespace Phpactor\Extension\LanguageServerIndexer\Status;

use Phpactor\AmpFsWatch\Watcher;
use Phpactor\Extension\LanguageServer\Status\StatusProvider;
use Phpactor\Indexer\IndexAgentBuilder;

class IndexerStatusProvider implements StatusProvider
{
    private Watcher $watcher;

    private IndexAgentBuilder $builder;


    public function __construct(Watcher $watcher, IndexAgentBuilder $builder)
    {
        $this->watcher = $watcher;
        $this->builder = $builder;
    }

    public function title(): string
    {
        return 'Indexer';
    }

    public function provide(): array
    {
        return [
            'watcher' => $this->watcher->describe(),
            'paths' => implode(', ', $this->builder->paths()),
            'include' => implode(', ', $this->builder->includePatterns()),
            'exclude' => implode(', ', $this->builder->excludePatterns())
        ];
    }
}
