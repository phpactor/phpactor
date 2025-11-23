<?php

namespace Phpactor\TextDocument\Tests\Unit\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\Util\LineAtOffset;

class LineAtOffsetTest extends TestCase
{
    #[DataProvider('provideLineAtOffset')]
    public function testLineAtOffset(string $text, string $expectedWord): void
    {
        [ $text, $offset ] = ExtractOffset::fromSource($text);
        $offset = (int) $offset;

        self::assertEquals($expectedWord, (new LineAtOffset())($text, --$offset));
    }

    /**
     * @return Generator<array{string, string}>
     */
    public static function provideLineAtOffset(): Generator
    {
        yield [
            'hello thi<>s is',
            'hello this is',
        ];
        yield 'first char' => [
            'h<>ello this is',
            'hello this is',
        ];
        yield 'last char' => [
            'hello this is<>',
            'hello this is',
        ];
        yield 'offset is newline' => [
            "hello this is\n<>",
            'hello this is',
        ];
        yield [
            <<<'EOT'
                <?php

                Hello
                Thi<>s is my line

                Thanks
                EOT
            , 'This is my line',
        ];
        yield 'multibyte 1' => [
            <<<'EOT'
                <?php

                转注字 / 轉注字
                转<>注字 / 轉注字

                Thanks
                EOT
            , '转注字 / 轉注字',
        ];
        yield 'multibyte 2' => [
            <<<'EOT'
                <?php

                转注字 / 轉注字
                转注字 / 轉注字<>

                Thanks
                EOT
            , '转注字 / 轉注字',
        ];
    }

    public function testOutOfRange(): void
    {
        $this->expectException(OutOfBoundsException::class);
        (new LineAtOffset())('a', 2);
    }
}
