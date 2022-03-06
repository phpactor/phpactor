<?php

namespace Phpactor\Indexer\Tests\Benchmark;

use Phpactor\Indexer\Model\Record\ClassRecord;

/**
 * @Iterations(33)
 * @Revs(1000)
 * @OutputTimeUnit("microseconds")
 */
class ClassRecordShortNameBench
{
    /**
     * @var ClassRecord
     */
    private $record;

    public function createClassRecord(): void
    {
        $this->record = ClassRecord::fromName('Barfoo\\Foobar');
    }

    /**
     * @BeforeMethods({"createClassRecord"})
     */
    public function benchShortName(): void
    {
        $this->record->shortName();
    }
}
