<?php

namespace Phpactor\Indexer\Tests\Benchmark;

use Phpactor\Indexer\Adapter\Php\FileSearchIndex;
use Phpactor\Indexer\Adapter\Tolerant\TolerantCompositeIndexer;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\SearchClient;

/**
 * Run ./bin/console index:build before running this benchmark
 *
 * @Iterations(10)
 * @Revs(1)
 * @OutputTimeUnit("time")
 */
class SearchBench
{
    private SearchClient $search;

    public function createBareFileSearch(): void
    {
        $indexPath = __DIR__ . '/../..';
        $this->search = new FileSearchIndex($indexPath . '/cache/search');
    }

    public function createFullFileSearch(): void
    {
        $indexPath = __DIR__ . '/../../cache';
        $this->search = IndexAgentBuilder::create(
            $indexPath,
            __DIR__ .'/../../',
            TolerantCompositeIndexer::create(),
        )
            ->buildAgent()->search();
    }

    /**
     * @BeforeMethods({"createBareFileSearch"})
     * @ParamProviders({"provideSearches"})
     */
    public function benchBareFileSearch(array $params): void
    {
        foreach ($this->search->search(new ShortNameBeginsWith($params['search'])) as $result) {
        }
    }

    /**
     * @BeforeMethods({"createFullFileSearch"})
     * F
     * @ParamProviders({"provideSearches"})
     */
    public function benchFullFileSearch(array $params): void
    {
        foreach ($this->search->search(new ShortNameBeginsWith($params['search'])) as $result) {
        }
    }

    public function provideSearches()
    {
        yield 'A' => [
            'search' => 'A',
        ];

        yield 'Request' => [
            'search' => 'Request',
        ];
    }
}
