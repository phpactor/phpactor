<?php

namespace Phpactor\Indexer\Adapter\Tolerant;

use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextDocument;
use Phpactor\Indexer\Model\Index;

interface TolerantIndexer
{
    public function canIndex(Node $node): bool;

    public function index(Index $index, TextDocument $document, Node $node): void;

    public function beforeParse(Index $index, TextDocument $document): void;
}
