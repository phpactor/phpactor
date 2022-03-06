<?php

namespace Phpactor\Indexer\Model;

interface SearchIndex extends SearchClient
{
    public function write(Record $record): void;

    public function remove(Record $record): void;

    public function flush(): void;
}
