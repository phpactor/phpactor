<?php

namespace Phpactor\TextDocument\Tests\Unit;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\EfficientLineCols;
use Phpactor\TextDocument\LineCol;

class EfficientLineColsTest extends TestCase
{
    /**
     * @dataProvider provideConvertLineColToOffset
     */
    public function testFromByteOffsets(array $offsets, string $text, Closure $assertion): void
    {
        $converter = EfficientLineCols::fromByteOffsetInts($text, $offsets);
        $assertion($converter);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConvertLineColToOffset(): Generator
    {
        yield [
            [],
            '',
            function (EfficientLineCols $lineCols) {
                self::assertInstanceOf(EfficientLineCols::class, $lineCols);
            }
        ];
        yield [
            [2],
            '01234',
            function (EfficientLineCols $lineCols) {
                self::assertEquals(3, $lineCols->get(2)->col());
                self::assertEquals(1, $lineCols->get(2)->line());
            }
        ];
        yield [
            [2, 3, 0, 10],
            "01234\n5678",
            function (EfficientLineCols $lineCols) {
                self::assertEquals(3, $lineCols->get(2)->col());
                self::assertEquals(1, $lineCols->get(2)->line());
                self::assertEquals(2, $lineCols->get(10)->line());
                self::assertEquals(5, $lineCols->get(10)->col());
            }
        ];
    }
}
