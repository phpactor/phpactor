<?php

namespace Phpactor\Indexer\Tests\Unit\Model\SearchIndex;

use Generator;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemorySearchIndex;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Model\SearchIndex\ValidatingSearchIndex;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\NullLogger;

class ValidatingSearchIndexTest extends IntegrationTestCase
{
    private InMemorySearchIndex $innerSearchIndex;

    private InMemoryIndex $index;

    private ValidatingSearchIndex $searchIndex;

    protected function setUp(): void
    {
        $this->innerSearchIndex = new InMemorySearchIndex();
        $this->index = new InMemoryIndex();
        $this->searchIndex = new ValidatingSearchIndex(
            $this->innerSearchIndex,
            $this->index,
            new NullLogger()
        );
    }

    public function testWillRemoveResultIfNotExistIndex(): void
    {
        $record = ClassRecord::fromName('Foobar');
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(0, $this->searchIndex->search(new ShortNameBeginsWith('Foobar')));
        self::assertFalse($this->innerSearchIndex->has($record));
    }

    public function testYieldsRecordsWithoutAPath(): void
    {
        $record = MemberRecord::fromIdentifier('method#foo');
        $this->index->write($record);
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(1, $this->searchIndex->search(new ShortNameBeginsWith('foo')));
    }

    public function testRemovesFromIndexIfFileDoesNotExist(): void
    {
        $record = ClassRecord::fromName('Foobar')
            ->setFilePath($this->workspacePath('nope.php'));

        $this->index->write($record);
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(0, $this->searchIndex->search(new ShortNameBeginsWith('Foobar')));
        self::assertFalse($this->innerSearchIndex->has($record));
    }

    public function testYieldsSearchResultIfFileExists(): void
    {
        $this->workspace()->put('yep.php', 'foo');
        $record = ClassRecord::fromName('Foobar')
            ->setFilePath($this->workspacePath('yep.php'));

        $this->index->write($record);
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(1, $this->searchIndex->search(new ShortNameBeginsWith('Foobar')));
        self::assertTrue($this->innerSearchIndex->has($record));
    }

    private static function assertSearchCount(int $int, Generator $generator): void
    {
        self::assertEquals($int, count(iterator_to_array($generator)));
    }

    private function workspacePath(string $string): TextDocumentUri
    {
        return TextDocumentUri::fromString($this->workspace()->path($string));
    }
}
