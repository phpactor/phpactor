<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\IndexAgent;

class RealIndexAgent implements IndexAgent, TestIndexAgent
{
    /**
     * @var QueryClient
     */
    private $query;

    /**
     * @var SearchClient
     */
    private $search;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Index
     */
    private $index;

    public function __construct(Index $index, QueryClient $query, SearchClient $search, Indexer $indexer)
    {
        $this->query = $query;
        $this->search = $search;
        $this->indexer = $indexer;
        $this->index = $index;
    }

    public function search(): SearchClient
    {
        return $this->search;
    }

    public function query(): QueryClient
    {
        return $this->query;
    }

    public function indexer(): Indexer
    {
        return $this->indexer;
    }

    public function index(): Index
    {
        return $this->index;
    }

    public function access(): IndexAccess
    {
        return $this->index;
    }
}
