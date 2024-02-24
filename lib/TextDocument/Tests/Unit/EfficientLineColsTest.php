<?php

namespace Phpactor\TextDocument\Tests\Unit;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\EfficientLineCols;

class EfficientLineColsTest extends TestCase
{
    /**
     * @dataProvider provideConvertOffsetsToLineCol
     * @param list<int> $offsets
     */
    public function testFromByteOffsets(array $offsets, string $text, Closure $assertion): void
    {
        $converter = EfficientLineCols::fromByteOffsetInts($text, $offsets);
        $assertion($converter);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConvertOffsetsToLineCol(): Generator
    {
        yield [
            [],
            '',
            function (EfficientLineCols $lineCols): void {
                self::assertInstanceOf(EfficientLineCols::class, $lineCols);
            }
        ];
        yield [
            [2],
            '01234',
            function (EfficientLineCols $lineCols): void {
                self::assertEquals(3, $lineCols->get(2)->col());
                self::assertEquals(1, $lineCols->get(2)->line());
            }
        ];
        yield [
            [2, 3, 0, 10],
            "01234\n5678",
            function (EfficientLineCols $lineCols): void {
                self::assertEquals(3, $lineCols->get(2)->col());
                self::assertEquals(1, $lineCols->get(2)->line());
                self::assertEquals(2, $lineCols->get(10)->line());
                self::assertEquals(5, $lineCols->get(10)->col());
            }
        ];
    }

    /**
     * @dataProvider provideConvertOffsetsToLineColAsOffset
     * @param list<int> $offsets
     */
    public function testFromByteOffsetsAsOffset(array $offsets, string $text, Closure $assertion): void
    {
        $converter = EfficientLineCols::fromByteOffsetInts($text, $offsets, true);
        $assertion($converter);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConvertOffsetsToLineColAsOffset(): Generator
    {
        yield 'cat' => [
            [5],
            'a😸bc',
            function (EfficientLineCols $lineCols): void {
                self::assertEquals(4, $lineCols->get(5)->col());
            }
        ];
        yield 'utf16' => [
            [46],
            <<<'PHP'
                <?php

                echo '👩👨👦👧' . invalid() . strlen('Lorem ipsum dolor sit amet');
                PHP,
            function (EfficientLineCols $lineCols): void {
                self::assertEquals(3, $lineCols->get(46)->line());
                self::assertEquals(32, $lineCols->get(46)->col());
            }
        ];
    }
}
