<?php

namespace Phpactor\Indexer\Tests\Benchmark\Model\Query\Criteria;

use Generator;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsOrCamelMatchesTo;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsOrFuzzilyMatchesWith;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsOrFuzzilyMatchesWith2;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameFuzzilyMatchesTo;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameFuzzilyMatchesTo2;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameMatchesToBench
{
    /**
     * @ParamProviders("provideSearch")
     * @Revs(1000)
     * @Iterations(5)
     * @param array{string, string} $data
     */
    public function benchRegexFuzzyMatching(array $data): void
    {
        $criteria = new ShortNameFuzzilyMatchesTo($data[0]);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

    /**
     * @ParamProviders("provideSearch")
     * @Revs(1000)
     * @Iterations(5)
     * @param array{string, string} $data
     */
    public function benchShortNameBeginsOrFuzzilyMatchesWith(array $data): void
    {
        $criteria = new ShortNameBeginsOrFuzzilyMatchesWith($data[0]);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

    /**
     * @ParamProviders("provideSearch")
     * @Revs(1000)
     * @Iterations(5)
     * @param array{string, string} $data
     */
    public function benchShortNameBeginsOrFuzzilyMatchesWith2(array $data): void
    {
        $criteria = new ShortNameBeginsOrFuzzilyMatchesWith2($data[0]);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

    /**
     * @ParamProviders("provideSearch")
     * @Revs(1000)
     * @Iterations(5)
     * @param array{string, string} $data
     */
    public function benchStringFuzzyMatching(array $data): void
    {
        $criteria = new ShortNameFuzzilyMatchesTo2($data[0]);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

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
    public function benchShortNameBeginsOrCamelMatchesTo(array $data): void
    {
        $criteria = new ShortNameBeginsOrCamelMatchesTo($data[0]);

        $record = ClassRecord::fromName($data[1]);

        $criteria->isSatisfiedBy($record);
    }

    public function provideSearch(): Generator
    {
        yield 'leading substring' => ['Bag', 'Foobar\\Bagno'];
        yield 'empty search' => ['', 'Foobar\\Bagno'];
        yield 'subsequence' => ['bgn', 'Foobar\\Bagno'];
        yield 'multibyte' => ['☠😼', 'Foobar\\😼☠k😼'];
    }
}
