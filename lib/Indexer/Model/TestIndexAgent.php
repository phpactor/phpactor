<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\IndexAgent;

interface TestIndexAgent extends IndexAgent
{
    public function index(): Index;
}
