<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextEditDiff;

class TextEditDiffTest extends TestCase
{
    #[DataProvider('provideDiff')]
    public function testDiff(string $one, string $two): void
    {
        $edits = (new TextEditDiff())->diff($one, $two);
        self::assertEquals(
            $two,
            $edits->apply($one)
        );
    }

    /**
      * @return Generator<string,array{string,string}>
      */
    public static function provideDiff(): Generator
    {
        yield 'add string' => [
            'foo',
            'foo bar',
        ];
        yield 'remove string' => [
            'foo bar',
            'foo',
        ];
        yield 'insert string' => [
            'foo bar',
            'foo baz boo bar bag',
        ];

        yield 'first char' => [
            'i',
            'b',
        ];

        yield 'differnet' => [
            'it little profits',
            'that an idle king',
        ];

        yield 'poem' => [
            implode("\n", [
                'it little profits that an idle king',
                'matched with an aged wife',
            ]),
            implode("\n", [
                'by this still hearth',
                'it little profits that an idle king',
            ])
        ];

        yield 'code' => [
            implode("\n", [
                '<?php',
                'class Foobar {',
                '    private $foobar;',
                '    /**',
                '     * @return array<int,foobar>',
                '     */',
                '    public function bar() {',
                '    }',
                '}'
            ]),
            implode("\n", [
                '<?php',
                'class Foobar {',
                '',
                '    /**',
                '     * @var Foobars',
                '     */',
                '    private $foobar;',
                '',
                '    /**',
                '     * @param array<int,foobar> $bar',
                '     * @return array<string,foobar>',
                '     */',
                '    public function bar(array $bar) {',
                '    }',
                '}'
            ]),
        ];
    }
}
