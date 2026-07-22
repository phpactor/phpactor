<?php

declare(strict_types=1);

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameMatchesTo;
use Phpactor\Indexer\Model\Record\ClassRecord;

class ShortNameMatchesToTest extends TestCase
{
    #[DataProvider('provideSearch')]
    public function testLeadingOnly(string $name, string $path, bool $expectedLeading, bool $expectedFuzzy): void
    {
        $record = ClassRecord::fromName($path);
        self::assertSame($expectedLeading, (new ShortNameMatchesTo($name, false))->isSatisfiedBy($record));
        self::assertSame($expectedFuzzy, (new ShortNameMatchesTo($name, true))->isSatisfiedBy($record));
    }

    /**
     * @return Generator<string,array{string,string,bool,bool}>
     */
    public static function provideSearch(): Generator
    {
        yield 'empty search' => ['', 'Foobar\\Bagno', false, false];
        yield 'no match' => ['Barfoo', 'Foobar\\Bazfoo', false, false];
        yield 'matches exact' => ['Barfoo', 'Foobar\\Barfoo', true, true];
        yield 'substring' => ['Bag', 'Foobar\\Bagno', true, true];
        yield 'subsequence' => ['bgn', 'Foobar\\Bagno', false, false];
        yield 'negative camel 1' => ['Shame', 'ShortNameBeginsWith', false, false];
        yield 'tolower leading' => ['short', 'ShortNameBeginsWith', true, true];
        yield 'camel 1' => ['ShBeg', 'ShortNameBeginsWith', false, true];
        yield 'camel 2' => ['hBeg', 'ShortNameBeginsWith', false, false];
        yield 'camel 3' => ['BegWit', 'ShortNameBeginsWith', false, true];
        yield 'camel only upper' => ['SBW', 'ShortNameBeginsWith', false, true];
        yield 'underscore in subject and phrase' => ['fil_g_c', 'file_get_contents', false, true];
        yield 'underscore only in subject' => ['filgc', 'file_get_contents', false, true];
        yield 'underscore in subject, negative' => ['fits', 'file_get_contents', false, false];
        yield 'multibyte' => ['😼☠', 'Foobar\\😼☠k😼', true, true];
        yield 'lower first' => ['gNT', 'getDescendantNodesAndTokens', false, true];
        yield 'only upper in subject' => ['tr', 'TARGET_CLASS', false, false];
        yield 'only upper in subject 2' => ['tc', 'TARGET_CLASS', false, false];
    }
}
