<?php

namespace Phpactor\Indexer\Model;

use Phpactor\TextDocument\TextDocument;

interface IndexBuilder
{
    public function index(Index $index, TextDocument $document): void;

    public function done(Index $index): void;
}
