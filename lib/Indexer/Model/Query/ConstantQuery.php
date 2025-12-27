<?php

namespace Phpactor\Indexer\Model\Query;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexQuery;
use Phpactor\Indexer\Model\Record\ConstantRecord;

class ConstantQuery implements IndexQuery
{
    public function __construct(private readonly Index $index)
    {
    }

    public function get(string $identifier): ?ConstantRecord
    {
        $prototype = ConstantRecord::fromName($identifier);
        return $this->index->has($prototype) ? $this->index->get($prototype) : null;
    }
}
