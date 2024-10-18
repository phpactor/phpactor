<?php

namespace Phpactor\Indexer\Tests\Unit\Model\SearchIndex;

use Prophecy\PhpUnit\ProphecyTrait;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\SearchIndex;
use Phpactor\Indexer\Model\SearchIndex\FilteredSearchIndex;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class FilteredSearchIndexTest extends IntegrationTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<SearchIndex>
     */
    private ObjectProphecy $innerIndex;

    private FilteredSearchIndex $index;

    protected function setUp(): void
    {
        $this->innerIndex = $this->prophesize(SearchIndex::class);
        $this->index = new FilteredSearchIndex($this->innerIndex->reveal(), [ClassRecord::RECORD_TYPE]);
    }

    public function testDecoration(): void
    {
        $this->innerIndex->search(new ShortNameBeginsWith('foobar'))->willYield([ClassRecord::fromName('Foobar')])->shouldBeCalled();
        $this->innerIndex->flush()->shouldBeCalled();
        $this->index->search(new ShortNameBeginsWith('foobar'));
        $this->index->flush();
    }

    public function testWritesRecordThatIsAllowed(): void
    {
        $this->innerIndex->write(ClassRecord::fromName('FOOBAR'))->shouldBeCalled();
        $this->index->write(ClassRecord::fromName('FOOBAR'));
    }

    public function testDoesNotWriteRecordsNotAllowed(): void
    {
        $this->innerIndex->write(Argument::any())->shouldNotBeCalled();
        $this->index->write(FunctionRecord::fromName('FOOBAR'));
    }
}
