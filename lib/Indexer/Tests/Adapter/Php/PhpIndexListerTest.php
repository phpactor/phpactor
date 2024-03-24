<?php

namespace Phpactor\Indexer\Tests\Adapter\Php;

use Phpactor\Indexer\Adapter\Php\PhpIndexerLister;
use Phpactor\Indexer\Tests\IntegrationTestCase;

class PhpIndexListerTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testListsIndexes(): void
    {
        $this->workspace()->mkdir('index1');
        $this->workspace()->mkdir('index2');
        $this->workspace()->put('file1.txt', '');

        $lister = $this->lister();

        $infos = iterator_to_array($lister->list());
        self::assertCount(2, $infos);
    }

    private function lister(): PhpIndexerLister
    {
        return (new PhpIndexerLister($this->workspace()->path()));
    }
}
