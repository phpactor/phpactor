<?php

namespace Phpactor\TextDocument\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class TextEditsTest extends TestCase
{
    /**
     * @dataProvider provideMerge
     * @param TextEdit[] $edits1
     * @param TextEdit[] $edits2
     * @param TextEdit[] $expectedEdits
     */
    public function testMerge(array $edits1, array $edits2, array $expectedEdits): void
    {
        self::assertEquals(
            TextEdits::fromTextEdits($expectedEdits),
            TextEdits::fromTextEdits($edits1)->merge(
                TextEdits::fromTextEdits($edits2)
            )
        );
    }

    /**
    * @return Generator<string, array{array<TextEdit>, array<TextEdit>, array<TextEdit>}>
    */
    public function provideMerge(): Generator
    {
        yield 'empty' => [
            [
            ],
            [
            ],
            [
            ],
        ];

        yield 'empty merge does not affect existing data' => [
            [
                TextEdit::create(1, 5, 'foobar'),
                TextEdit::create(2, 5, 'foobar'),
            ],
            [
            ],
            [
                TextEdit::create(1, 5, 'foobar'),
                TextEdit::create(2, 5, 'foobar'),
            ],
        ];

        yield 'original edits are ordered before subsequent edits with same offset' => [
            [
                TextEdit::create(1, 5, 'foobar'),
                TextEdit::create(2, 5, 'foobar'),
            ],
            [
                TextEdit::create(1, 5, 'barfoo'),
                TextEdit::create(2, 5, 'barfoo'),
            ],
            [
                TextEdit::create(1, 5, 'foobar'),
                TextEdit::create(1, 5, 'barfoo'),
                TextEdit::create(2, 5, 'foobar'),
                TextEdit::create(2, 5, 'barfoo'),
            ],
        ];

        yield 'text edits are sorted' => [
            [
                TextEdit::create(2, 5, 'foobar'),
                TextEdit::create(3, 5, 'foobar'),
            ],
            [
                TextEdit::create(1, 5, 'barfoo'),
                TextEdit::create(2, 5, 'barfoo'),
            ],
            [
                TextEdit::create(1, 5, 'barfoo'),
                TextEdit::create(2, 5, 'foobar'),
                TextEdit::create(2, 5, 'barfoo'),
                TextEdit::create(3, 5, 'foobar'),
            ],
        ];
    }

    /**
     * @dataProvider provideApplyTextEdits
     */
    public function testApplyTextEdits(string $source, TextEdits $textEdits, string $expected): void
    {
        self::assertEquals(
            $expected,
            $textEdits->apply($source)
        );
    }

    /**
     * @return Generator<string, array{string, TextEdits, string}>
     */
    public function provideApplyTextEdits(): Generator
    {
        yield 'nothing' => [
            '',
            TextEdits::none(),
            ''
        ];

        yield 'insert' => [
            '',
            TextEdits::one(TextEdit::create(0, 0, 'hello')),
            'hello'
        ];

        yield 'delete' => [
            'delete',
            TextEdits::one(TextEdit::create(0, 6, '')),
            ''
        ];

        yield 'replace' => [
            'delete',
            TextEdits::one(TextEdit::create(0, 6, 'foobar')),
            'foobar'
        ];

        yield 'multiple edits at same offset' => [
            'hello ',
            TextEdits::fromTextEdits([
                TextEdit::create(6, 0, 'world'),
                TextEdit::create(6, 0, ' how'),
                TextEdit::create(6, 0, ' you'),
                TextEdit::create(6, 0, ' do'),
            ]),
            'hello world how you do'
        ];
    }

    /**
     * @dataProvider provideApplyTextEditsErrors
     */
    public function testApplyTextEditsErrors(string $source, TextEdits $textEdits, string $expectedMessage): void
    {
        $this->expectExceptionMessage($expectedMessage);
        $textEdits->apply($source);
    }

    /**
     * @return Generator<string, array{string, TextEdits, string}>
     */
    public function provideApplyTextEditsErrors(): Generator
    {
        yield 'shows debug information' => [
            'hello      ',
            TextEdits::fromTextEdits([
                TextEdit::create(1, 4, 'world'),
                TextEdit::create(2, 8, ' how'),
            ]),
            '> 1 5 "world"',
        ];

        yield 'overlapping text edits disallowed' => [
            'hello      ',
            TextEdits::fromTextEdits([
                TextEdit::create(1, 4, 'world'),
                TextEdit::create(2, 8, ' how'),
            ]),
            'Overlapping',
        ];

        yield 'out of bounds text edit' => [
            'hello',
            TextEdits::fromTextEdits([
                TextEdit::create(10, 4, 'world'),
            ]),
            'Text edit end',
        ];
    }

    public function testCreateOneConstructor(): void
    {
        self::assertInstanceOf(TextEdits::class, TextEdits::one(TextEdit::create(10, 10, 'f')));
    }

    public function testCreateNoneConstructor(): void
    {
        self::assertInstanceOf(TextEdits::class, TextEdits::none());
    }

    public function testAddTextEdit(): void
    {
        self::assertEquals(
            TextEdits::none()->add(TextEdit::create(10, 10, 'asd')),
            TextEdits::one(TextEdit::create(10, 10, 'asd'))
        );
    }
}
