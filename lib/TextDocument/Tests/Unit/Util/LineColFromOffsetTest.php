<?php

namespace Phpactor\TextDocument\Tests\Unit\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\Util\LineColFromOffset;

class LineColFromOffsetTest extends TestCase
{
    /**
     * @dataProvider provideLineColFromOffset
     */
    public function testLineColFromOffset(
        string $document,
        int $offset,
        int $expectedLine,
        int $expectedCol
    ): void {
        $lineCol = (new LineColFromOffset())($document, $offset);
        assert($lineCol instanceof LineCol);
        $this->assertEquals($expectedLine, $lineCol->line(), 'line no');
        $this->assertEquals($expectedCol, $lineCol->col(), 'col no');
        $this->assertEquals($offset, $lineCol->toByteOffset($document)->toInt(), 'reverse to byte offset');
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLineColFromOffset(): Generator
    {
        yield 'hello 1' => [
            'hello',
            0,
            1,
            1
        ];

        yield 'hello 2' => [
            'hello',
            2,
            1,
            3
        ];

        yield 'multiline' => [
            "hello\ngoodbye",
            8,
            2,
            3,
        ];

        yield 'multiline 2' => [
            "12\n345\n678",
            4,
            2,
            2
        ];

        yield '2 lines with special chars' => [
            "h转llo\ngoodbye",
            10,
            2,
            3
        ];

        yield '4 lines with special chars' => [
            <<<'EOT'
                h转llo
                goodbye
                h转llo
                goodbye
                EOT
        ,
            26,
            4,
            3
        ];
    }

    public function testOutOfBoundsLineCol(): void
    {
        $lineCol = (new LineColFromOffset())(<<<'EOT'
            foo
            bar
            EOT
        , 10);
        assert($lineCol instanceof LineCol);
        self::assertEquals(2, $lineCol->line());
        self::assertEquals(4, $lineCol->col());
    }
}
