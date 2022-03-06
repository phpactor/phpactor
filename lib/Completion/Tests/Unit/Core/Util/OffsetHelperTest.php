<?php

namespace Phpactor\Completion\Tests\Unit\Core\Util;

use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TestUtils\ExtractOffset;

class OffsetHelperTest extends TestCase
{
    /**
     * @dataProvider provideReturnsLastNonWhitespaceOffset
     */
    public function testReturnsLastNonWhitespaceOffset(string $example): void
    {
        list($source, $expectedOffset) = ExtractOffset::fromSource($example);
        $characterOffset = OffsetHelper::lastNonWhitespaceCharacterOffset($source);

        $this->assertEquals(
            $expectedOffset,
            strlen(mb_substr($source, 0, $characterOffset)),
            'Character offset corresponds to correct byte offset'
        );
    }

    public function provideReturnsLastNonWhitespaceOffset()
    {
        yield 'empty string' => [
            '',
        ];

        yield 'no extra whitespace' => [
            'foobar<>',
        ];

        yield 'extra whitespace' => [
            'foobar<>    ',
        ];

        yield 'extra newline' => [
            'foobar<>' . PHP_EOL,
        ];

        yield 'extra windows newline' => [
            "foobar<>\r\n",
        ];

        yield 'multi-byte chars' => [
            "fȯøbar<>\r\n",
        ];

        yield 'extra tab' => [
            "foobar<>\t",
        ];

        yield 'long string (about 6MB)' => [
            str_repeat('foobar', 2**20) . "<>\t",
            'this is actually unused'
        ];
    }
}
