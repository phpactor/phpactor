<?php

namespace Phpactor\Indexer\Tests\Adapter\Tolerant;

use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexer;
use Phpactor\Indexer\Model\TestIndexAgent;
use Phpactor\Indexer\Tests\IntegrationTestCase;

class TolerantIndexerTestCase extends IntegrationTestCase
{
    /**
     * @param list<TolerantIndexer>|TolerantIndexer $indexer
     */
    protected function runIndexer(array|TolerantIndexer $indexer, string $path): TestIndexAgent
    {
        // run the indexer twice - the results should not be affected
        $this->doRunIndexer($indexer, $path);
        return $this->doRunIndexer($indexer, $path);
    }

    /**
     * @param list<TolerantIndexer>|TolerantIndexer $indexer
     */
    private function doRunIndexer(array|TolerantIndexer $indexer, string $path): TestIndexAgent
    {
        $indexer = is_array($indexer) ? $indexer : [$indexer];
        $agent = $this->indexAgentBuilder('src')
            ->setIndexers((array)$indexer)->buildTestAgent();

        $agent->indexer()->getJob()->run();

        return $agent;
    }
}
