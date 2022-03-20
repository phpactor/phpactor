<?php

namespace Phpactor\TextDocument\Tests\Unit\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\Util\WordAtOffset;

class WordAtOffsetTest extends TestCase
{
    /**
     * @dataProvider provideWordAtOffset
     */
    public function testWordAtOffset(string $text, string $expectedWord, string $split = WordAtOffset::SPLIT_WORD): void
    {
        [ $text, $offset ] = ExtractOffset::fromSource($text);
        $offset = (int)$offset;

        $this->assertEquals($expectedWord, (new WordAtOffset($split))($text, $offset));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideWordAtOffset(): Generator
    {
        yield [
            'hello thi<>s is',
            'this',
        ];

        yield [
            'h<>ello this is',
            'hello',
        ];
        yield [
            'hello this i<>s',
            'is',
        ];
        yield [
            'hello this is<>',
            'is',
        ];
        yield [
            'hello this is <>',
            ' ',
        ];
        yield [
            'hello this <>is',
            ' ',
        ];
        yield [
            "hello this is\nsom<>ething",
            'something',
        ];
        yield [
            " <>  hello this is\nsom<>ething",
            ' ',
        ];
        yield [
            'Reque<>st;',
            'Request',
        ];
        yield [
            "Foobar\Reque<>st;",
            'Request',
        ];
        yield 'qualified name' => [
            "Foobar\Reque<>st;",
            'Foobar\Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'nullable type' => [
            '?Reque<>st;',
            'Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'trailing comma' => [
            'Reque<>st,',
            'Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'pipe type separator' => [
            'Reque<>st|null,',
            'Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'annotations' => [
            '@Reque<>st',
            'Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'subannotations (removing equal)' => [
             '* input=Re<>quest::class',
            'Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'templated type' => [
             'array<Re<>quest>',
            'Request',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
        yield 'constant' => [
            <<<'EOT'
                    /**
                     * @SWG\Post(
                     *     @SWG\Response(
                     *         response=Resp<>onse::HTTP_OK,
                     *         description="Reset password sent successfully"
                     * )
                     */
                EOT
            , 'Response',
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ];
    }
}
