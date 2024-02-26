<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsOrFuzzilyMatchesWith;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsOrFuzzilyMatchesWith2;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameFuzzilyMatchesTo;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameFuzzilyMatchesTo2;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameFuzzilyMatchesToTest extends TestCase
{
    /**
     * @dataProvider provideSearch
     */
    public function testFuzzyRegex(string $name, string $path, bool $expected): void
    {
        $record = ClassRecord::fromName($path);
        self::assertTrue((new ShortNameFuzzilyMatchesTo($name))->isSatisfiedBy($record) === $expected);
    }

    /**
     * @dataProvider provideSearch
     */
    public function testFuzzyString(string $name, string $path, bool $expected): void
    {
        $record = ClassRecord::fromName($path);
        self::assertTrue((new ShortNameFuzzilyMatchesTo2($name))->isSatisfiedBy($record) === $expected);
    }

    /**
     * @dataProvider provideSearch
     */
    public function testFuzzyStringOrRegex(string $name, string $path, bool $expected): void
    {
        $record = ClassRecord::fromName($path);
        self::assertTrue((new ShortNameBeginsOrFuzzilyMatchesWith($name))->isSatisfiedBy($record) === $expected);
    }

    /**
     * @dataProvider provideSearch
     */
    public function testFuzzyStringOrRegex2(string $name, string $path, bool $expected): void
    {
        $record = ClassRecord::fromName($path);
        self::assertTrue((new ShortNameBeginsOrFuzzilyMatchesWith2($name))->isSatisfiedBy($record) === $expected);
    }

    public function provideSearch(): Generator
    {
        yield 'empty search' => ['', 'Foobar\\Bagno', false];
        yield 'no match' => ['Barfoo', 'Foobar\\Bazfoo', false];
        yield 'matches exact' => ['Barfoo', 'Foobar\\Barfoo', true];
        yield 'substring' => ['Bag', 'Foobar\\Bagno', true];
        yield 'subsequence' => ['bgn', 'Foobar\\Bagno', true];
        yield 'multibyte' => ['☠😼', 'Foobar\\😼☠k😼', true];
    }
}
