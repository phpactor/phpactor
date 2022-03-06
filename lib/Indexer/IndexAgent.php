<?php

namespace Phpactor\Indexer;

use Phpactor\Indexer\Model\IndexAccess;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Model\SearchClient;

interface IndexAgent
{
    public function search(): SearchClient;

    public function query(): QueryClient;

    public function indexer(): Indexer;

    public function access(): IndexAccess;
}
