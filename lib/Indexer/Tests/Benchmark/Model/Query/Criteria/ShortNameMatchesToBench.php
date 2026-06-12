<?php

namespace Phpactor\Indexer\Tests\Benchmark\Model\Query\Criteria;

use Generator;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameMatchesTo;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameMatchesToBench
{
    /**
     * @ParamProviders("provideSearch")
     * @Revs(1000)
     * @Iterations(5)
     * @param array{string, string} $data
     */
    public function benchBeginsWith(array $data): void
    {
        $criteria = new ShortNameBeginsWith($data[0]);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

    /**
     * @ParamProviders("provideSearch")
     * @Revs(1000)
     * @Iterations(5)
     * @param array{string, string} $data
     */
    public function benchShortNameMatchesTo(array $data): void
    {
        $criteria = new ShortNameMatchesTo($data[0], true);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideSearch(): Generator
    {
        yield 'leading substring' => ['Bag', 'Foobar\\Bagno'];
        yield 'empty search' => ['', 'Foobar\\Bagno'];
        yield 'subsequence' => ['bgn', 'Foobar\\Bagno'];
        yield 'multibyte' => ['☠😼', 'Foobar\\😼☠k😼'];
    }
}
