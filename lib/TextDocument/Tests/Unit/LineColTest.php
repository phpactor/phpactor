<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\LineCol;

class LineColTest extends TestCase
{
    #[DataProvider('provideConvertLineColToOffset')]
    public function testToByteOffset(string $text, LineCol $lineCol, int $expectedOffset, ?string $sanityCheck = null): void
    {
        $this->assertEquals($expectedOffset, $lineCol->toByteOffset($text)->toInt());
        if ($sanityCheck) {
            self::assertEquals($sanityCheck, substr($text, 0, $lineCol->toByteOffset($text)->toInt()));
        }
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideConvertLineColToOffset(): Generator
    {
        yield [
            '',
            new LineCol(1, 1),
            0,
        ];

        yield [
            'a',
            new LineCol(1, 1),
            0,
        ];

        yield 'new line' => [
            "\na",
            new LineCol(2, 1),
            1,
        ];

        yield 'multi-byte 1' => [
            'ᅑa',
            new LineCol(1, 2),
            3,
            'ᅑ',
        ];

        yield 'multi-byte 2' => [
            'ᅑacd',
            new LineCol(1, 3),
            4,
            'ᅑa'
        ];

        yield 'multi-byte 3' => [
            "ᅑ\nacd",
            new LineCol(2, 2),
            5,
            "ᅑ\na"
        ];
    }

    public function testOutOfBoundsToByteOffset(): void
    {
        $lineCol = new LineCol(10, 20);
        assert($lineCol instanceof LineCol);
        self::assertEquals(13, $lineCol->toByteOffset("foobar\nbarfoo")->toInt());
    }
}
