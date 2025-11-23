<?php

namespace Phpactor\Indexer\Tests\Unit\Model\SearchIndex;

use Phpactor\Indexer\Adapter\Php\InMemory\InMemorySearchIndex;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\SearchIndex\SearchIncludeIndex;
use PHPUnit\Framework\TestCase;

class SearchIncludeIndexTest extends TestCase
{
    public function testInclude(): void
    {
        $index = new SearchIncludeIndex(InMemorySearchIndex::fromRecords(
            new ClassRecord('Foo\\Bar\\Baz'),
            new FunctionRecord('Foo\\Bar\\baz'),
            new ConstantRecord('Foo\\Bar\\BAZ'),
            new ClassRecord('Baz\\Bar\\BAZ'),
        ), ['^Foo\\\\']);

        $records = iterator_to_array($index->search(Criteria::or(
            Criteria::fqnBeginsWith('Foo'),
            Criteria::fqnBeginsWith('Baz'),
        )));
        self::assertCount(3, $records);
    }
}
