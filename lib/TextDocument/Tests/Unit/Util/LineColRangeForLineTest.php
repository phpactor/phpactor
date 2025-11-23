<?php

namespace Phpactor\TextDocument\Tests\Unit\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\LineColRange;
use Phpactor\TextDocument\Util\LineColRangeForLine;

class LineColRangeForLineTest extends TestCase
{
    #[DataProvider('provideRangeForLine')]
    public function testRangeForLine(string $text, int $lineNo, LineColRange $expected): void
    {
        self::assertEquals($expected, (new LineColRangeForLine())->rangeFromLine($text, $lineNo));
    }

    /**
     * @return Generator<array{string, int, LineColRange}>
     */
    public static function provideRangeForLine(): Generator
    {
        yield [
            'one',
            1,
            new LineColRange(
                new LineCol(1, 1),
                new LineCol(1, 3),
            )
        ];
        yield [
            '  one',
            1,
            new LineColRange(
                new LineCol(1, 3),
                new LineCol(1, 5),
            )
        ];
        yield [
            '  one  ',
            1,
            new LineColRange(
                new LineCol(1, 3),
                new LineCol(1, 5),
            )
        ];
        yield [
            "  one  \n  two  \n",
            2,
            new LineColRange(
                new LineCol(2, 3),
                new LineCol(2, 5),
            )
        ];

        yield 'empty line' => [
            "  one  \n  two  \n",
            3,
            new LineColRange(
                new LineCol(3, 1),
                new LineCol(3, 1),
            )
        ];

        yield 'out of range' => [
            "  one  \n  two  \n",
            4,
            new LineColRange(
                new LineCol(4, 1),
                new LineCol(4, 1),
            )
        ];
    }
}
