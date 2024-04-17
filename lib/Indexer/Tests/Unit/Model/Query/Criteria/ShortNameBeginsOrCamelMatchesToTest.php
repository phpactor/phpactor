<?php

declare(strict_types=1);

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameMatchesTo;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameBeginsOrCamelMatchesToTest extends TestCase
{
    /**
     * @dataProvider provideSearch
     */
    public function testCamel(string $name, string $path, bool $expected): void
    {
        $record = ClassRecord::fromName($path);
        self::assertTrue((new ShortNameMatchesTo($name))->isSatisfiedBy($record) === $expected);
    }

    public function provideSearch(): Generator
    {
        yield 'empty search' => ['', 'Foobar\\Bagno', false];
        yield 'no match' => ['Barfoo', 'Foobar\\Bazfoo', false];
        yield 'matches exact' => ['Barfoo', 'Foobar\\Barfoo', true];
        yield 'substring' => ['Bag', 'Foobar\\Bagno', true];
        yield 'subsequence' => ['bgn', 'Foobar\\Bagno', false];
        yield 'negative camel 1' => ['Shame', 'ShortNameBeginsWith', false];
        yield 'tolower leading' => ['short', 'ShortNameBeginsWith', true];
        yield 'camel 1' => ['ShBeg', 'ShortNameBeginsWith', true];
        yield 'camel 2' => ['hBeg', 'ShortNameBeginsWith', false];
        yield 'camel 3' => ['BegWit', 'ShortNameBeginsWith', true];
        yield 'camel only upper' => ['SBW', 'ShortNameBeginsWith', true];
        yield 'underscore in subject and phrase' => ['fil_g_c', 'file_get_contents', true];
        yield 'underscore only in subject' => ['filgc', 'file_get_contents', true];
        yield 'underscore in subject, negative' => ['fits', 'file_get_contents', false];
        yield 'multibyte' => ['😼☠', 'Foobar\\😼☠k😼', true];
        yield 'lower first' => ['gNT', 'getDescendantNodesAndTokens', true];
        yield 'only upper in subject' => ['tr', 'TARGET_CLASS', false];
        yield 'only upper in subject 2' => ['tc', 'TARGET_CLASS', false];
    }
}
