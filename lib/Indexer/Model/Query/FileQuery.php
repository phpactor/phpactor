<?php

namespace Phpactor\Indexer\Model\Query;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexQuery;
use Phpactor\Indexer\Model\Record\FileRecord;

class FileQuery implements IndexQuery
{
    /**
     * @var Index
     */
    private $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function get(string $identifier): ?FileRecord
    {
        $prototype = FileRecord::fromPath($identifier);
        return $this->index->has($prototype) ? $this->index->get($prototype) : null;
    }
}
