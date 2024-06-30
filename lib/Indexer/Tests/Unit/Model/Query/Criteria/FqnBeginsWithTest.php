<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;

class FqnBeginsWithTest extends TestCase
{
    public function testNotMatchesEmpty(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue(Criteria::fqnBeginsWith('Foobar')->isSatisfiedBy($record));
    }

    public function testMatchesExact(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo');
        self::assertTrue(Criteria::fqnBeginsWith('Foobar\\Barfoo')->isSatisfiedBy($record));
    }

    public function testNotMatches(): void
    {
        $record = ClassRecord::fromName('Foobar\\Bazfoo');
        self::assertFalse(Criteria::fqnBeginsWith('Barfoo')->isSatisfiedBy($record));
    }

    public function testMatchesPartialBeginningWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoos');
        self::assertTrue(Criteria::fqnBeginsWith('Foo')->isSatisfiedBy($record));
    }

    public function testNotMatchesPartialEndsWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\abBarfoo');
        self::assertFalse(Criteria::fqnBeginsWith('Barfoo')->isSatisfiedBy($record));
    }

    public function testMatchesGlobal(): void
    {
        $record = ClassRecord::fromName('Barfoo');
        self::assertTrue(Criteria::fqnBeginsWith('Barfoo')->isSatisfiedBy($record));
    }
}
