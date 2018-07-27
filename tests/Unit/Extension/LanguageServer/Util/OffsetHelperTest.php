<?php

namespace Phpactor\Tests\Unit\Extension\LanguageServer\Util;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Util\OffsetHelper;

class OffsetHelperTest extends TestCase
{
    /**
     * @dataProvider provideLineToOffset
     */
    public function testLineToOffset(string $text, int $line, int $char, int $expected)
    {
        $offset = OffsetHelper::lineAndCharacterNumberToOffset($text, $line, $char);
        $this->assertEquals($expected, $offset);
    }

    public function provideLineToOffset()
    {
        yield 'Empty' => [
            '', 0, 0, 1
        ];

        yield 'char 3' => [
            'hello', 0, 3, 4
        ];

        yield 'line 3 char 0' => [
            <<<'EOT'
hello
hello
hello
EOT
            , 2, 0, 14
        ];

        yield 'line 3 char 0' => [
            <<<'EOT'
hello
hello
hello
EOT
            , 2, 4, 16
        ];
    }

    public function testThrowsExceptionNegativeLineNo()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Line number');
        OffsetHelper::lineAndCharacterNumberToOffset('', -4, 0);
    }

    public function testThrowsExceptionNegativeColNo()
    {
        $this->expectExceptionMessage('Col number');
        $this->expectException(OutOfBoundsException::class);
        $offset = OffsetHelper::lineAndCharacterNumberToOffset('', 0, -4);
    }

    public function testInvalidLineNumber()
    {
        $this->expectExceptionMessage('Invalid line number');
        $this->expectException(OutOfBoundsException::class);
        $offset = OffsetHelper::lineAndCharacterNumberToOffset('', 2, 0);
    }

    public function testInvalidColNumber()
    {
        $this->expectExceptionMessage('Invalid character offset');
        $this->expectException(OutOfBoundsException::class);
        $offset = OffsetHelper::lineAndCharacterNumberToOffset('', 0, 2);
    }
}
