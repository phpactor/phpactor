<?php

namespace Phpactor\Indexer\Model;

interface IndexQuery
{
    /**
     * @return Record|null
     */
    public function get(string $identifier);
}
