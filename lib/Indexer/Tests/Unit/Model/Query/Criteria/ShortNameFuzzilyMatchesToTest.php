<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameFuzzilyMatchesTo;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameFuzzilyMatchesToTest extends TestCase
{
    public function testNotMatchesEmpty(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertFalse(Criteria::shortNameFuzzilyMatchesTo('')->isSatisfiedBy($record));
    }

    public function testMatchesExact(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue((new ShortNameFuzzilyMatchesTo('Barfoo'))->isSatisfiedBy($record));
    }

    public function testNotMatches(): void
    {
        $record = ClassRecord::fromName('Foobar\\Bazfoo');
        self::assertFalse((new ShortNameFuzzilyMatchesTo('Barfoo'))->isSatisfiedBy($record));
    }

    public function testSubsequence(): void
    {
        $record = ClassRecord::fromName('Foobar\\Bagno');
        self::assertTrue((new ShortNameFuzzilyMatchesTo('bgn'))->isSatisfiedBy($record));
    }
}
