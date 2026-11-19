<?php

namespace Phpactor\Indexer\Tests\Adapter\Php;

use Phpactor\Indexer\Adapter\Php\FileSearchIndex;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Tests\IntegrationTestCase;

class FileSearchIndexTest extends IntegrationTestCase
{
    private FileSearchIndex $index;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->index = new FileSearchIndex($this->workspace()->path('search'));
    }

    public function testWriteIndex(): void
    {
        $record = ClassRecord::fromName('Foobar');
        $this->index->write($record);
        $this->index->flush();

        $this->assertCount(1, $this->search('Foobar'));
    }

    public function testWriteIndexMultipleTimesDoesNotIncreaseSearchResultNumber(): void
    {
        $record = ClassRecord::fromName('Foobar');
        $this->index->write($record);
        $this->index->write($record);
        $this->index->write($record);
        $this->index->write($record);
        $this->index->flush();

        $this->assertCount(1, $this->search('Foobar'));
    }

    public function testMultipleResultsForPartialMatch(): void
    {
        $record = ClassRecord::fromName('Foobar');
        $this->index->write($record);
        $record = ClassRecord::fromName('Foostar');
        $this->index->write($record);
        $this->index->flush();

        $this->assertCount(2, $this->search('Foo'));
    }

    public function testUnchangedRecordsDoNotRewriteIndex(): void
    {
        $this->index->write(ClassRecord::fromName('Foobar'));
        $this->index->flush();

        $index = new FileSearchIndex($this->workspace()->path('search'));
        $index->write(ClassRecord::fromName('Foobar'));
        $index->remove(ClassRecord::fromName('Barfoo'));
        unlink($this->workspace()->path('search'));
        $index->flush();

        self::assertFileDoesNotExist($this->workspace()->path('search'));
    }

    public function testRemoveRecordFromIndexOnDisk(): void
    {
        $this->index->write(ClassRecord::fromName('Foobar'));
        $this->index->flush();

        $index = new FileSearchIndex($this->workspace()->path('search'));
        $index->remove(ClassRecord::fromName('Foobar'));
        $index->flush();

        $this->index = new FileSearchIndex($this->workspace()->path('search'));
        self::assertCount(0, $this->search('Foobar'));
    }

    private function search(string $query): array
    {
        return iterator_to_array($this->index->search(new ShortNameBeginsWith($query)));
    }
}
