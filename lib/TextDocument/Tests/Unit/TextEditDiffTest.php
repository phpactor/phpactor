<?php

namespace Phpactor\TextDocument\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextEditDiff;

class TextEditDiffTest extends TestCase
{
    /**
     * @dataProvider provideDiff
     */
    public function testDiff(string $one, string $two): void
    {
        self::assertEquals(
            $two,
            (new TextEditDiff())->diff($one, $two)->apply($one)
        );
   }

    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideDiff(): Generator
    {
        yield 'add string' => [
            'foo',
            'foo bar',
        ];
        yield 'remove string' => [
            'foo bar',
            'foo',
        ];
    }
}
