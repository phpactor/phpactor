<?php

namespace Phpactor\Indexer\Model;

use Generator;

interface IndexLister
{
    /**
     * @return Generator<IndexInfo>
     */
    public function list(): Generator;
}
