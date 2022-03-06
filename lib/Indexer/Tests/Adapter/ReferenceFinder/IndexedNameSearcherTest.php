<?php

namespace Phpactor\Indexer\Tests\Adapter\ReferenceFinder;

use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedNameSearcher;
use Phpactor\Indexer\Tests\Adapter\IndexTestCase;
use Phpactor\ReferenceFinder\Search\NameSearchResult;

class IndexedNameSearcherTest extends IndexTestCase
{
    public function testSearcher(): void
    {
        $this->workspace()->put('project/Foobar.php', '<?php class Foobar {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        foreach ($searcher->search('Foo') as $result) {
            assert($result instanceof NameSearchResult);
            self::assertEquals('Foobar', $result->name()->head()->__toString());
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
        }
    }
}
