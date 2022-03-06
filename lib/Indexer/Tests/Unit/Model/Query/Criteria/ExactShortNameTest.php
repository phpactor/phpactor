<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Query\Criteria\ExactShortName;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ExactShortNameTest extends TestCase
{
    public function testNotMatchesEmpty(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertFalse(Criteria::exactShortName('')->isSatisfiedBy($record));
    }

    public function testMatches(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue((new ExactShortName('Barfoo'))->isSatisfiedBy($record));
    }

    public function testNotMatches(): void
    {
        $record = ClassRecord::fromName('Foobar\\Bazfoo');
        self::assertFalse((new ExactShortName('Barfoo'))->isSatisfiedBy($record));
    }

    public function testNotMatchesPartial(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoos');
        self::assertFalse((new ExactShortName('Barfoo'))->isSatisfiedBy($record));
    }

    public function testMatchesGlobal(): void
    {
        $record = ClassRecord::fromName('Barfoo');
        self::assertTrue((new ExactShortName('Barfoo'))->isSatisfiedBy($record));
    }
}
