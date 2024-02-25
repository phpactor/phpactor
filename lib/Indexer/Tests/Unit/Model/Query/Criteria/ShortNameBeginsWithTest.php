<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameBeginsWithTest extends TestCase
{
    public function testNotMatchesEmpty(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue(Criteria::shortNameBeginsWith('Barfoo')->isSatisfiedBy($record));
    }

    public function testMatchesExact(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue((new ShortNameBeginsWith('Barfoo'))->isSatisfiedBy($record));
    }

    public function testMatchesCaseInsensitive(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue((new ShortNameBeginsWith('barfoo'))->isSatisfiedBy($record));
    }

    public function testNotMatches(): void
    {
        $record = ClassRecord::fromName('Foobar\\Bazfoo');
        self::assertFalse((new ShortNameBeginsWith('Barfoo'))->isSatisfiedBy($record));
    }

    public function testMatchesPartialBeginingWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoos');
        self::assertTrue((new ShortNameBeginsWith('Barfoo'))->isSatisfiedBy($record));
    }

    public function testNotMatchesPartialEndsWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\abBarfoo');
        self::assertFalse((new ShortNameBeginsWith('Barfoo'))->isSatisfiedBy($record));
    }

    public function testMatchesGlobal(): void
    {
        $record = ClassRecord::fromName('Barfoo');
        self::assertTrue((new ShortNameBeginsWith('Barfoo'))->isSatisfiedBy($record));
    }
}
