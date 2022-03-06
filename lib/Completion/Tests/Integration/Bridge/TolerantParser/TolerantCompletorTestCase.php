<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser;

use Phpactor\Completion\Bridge\TolerantParser\ChainTolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Tests\Integration\CompletorTestCase;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class TolerantCompletorTestCase extends CompletorTestCase
{
    abstract protected function createTolerantCompletor(TextDocument $source): TolerantCompletor;

    protected function createCompletor(string $source): Completor
    {
        $source = TextDocumentBuilder::create($source)->uri('file:///tmp/test')->build();
        return new ChainTolerantCompletor([
            $this->createTolerantCompletor($source)
        ]);
    }
}
