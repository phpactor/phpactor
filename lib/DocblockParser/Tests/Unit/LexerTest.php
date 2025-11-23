<?php

namespace Phpactor\DocblockParser\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Ast\Token;

class LexerTest extends TestCase
{
    /**
     * @param list<array{string,string}> $expectedTokens
     */
    #[DataProvider('provideLex')]
    public function testLex(string $lex, array $expectedTokens): void
    {
        $tokens = (new Lexer())->lex($lex)->toArray();

        self::assertCount(count($expectedTokens), $tokens, 'Expected number of tokens');

        foreach ($tokens as $index => $token) {
            [$type, $value] = $expectedTokens[$index];
            $expectedToken = new Token($token->byteOffset, $type, $value);
            self::assertEquals($expectedToken, $token);
        }
    }

    /**
     * @return Generator<array{string, array<array{string, string}>}>
     */
    public static function provideLex(): Generator
    {
        yield [ '', [] ];
        yield [
            <<<'EOT'
                /**
                 * Hello this is
                 * Multi
                 */
                EOT

            ,[
                [Token::T_PHPDOC_OPEN, '/**'],
                [Token::T_WHITESPACE, "\n"],
                [Token::T_ASTERISK, ' * '],
                [Token::T_LABEL, 'Hello'],
                [Token::T_WHITESPACE, ' '],
                [Token::T_LABEL, 'this'],
                [Token::T_WHITESPACE, ' '],
                [Token::T_LABEL, 'is'],
                [Token::T_WHITESPACE, "\n"],
                [Token::T_ASTERISK, ' * '],
                [Token::T_LABEL, 'Multi'],
                [Token::T_WHITESPACE, "\n"],
                [Token::T_WHITESPACE, ' '],
                [Token::T_PHPDOC_CLOSE, '*/'],
            ]
        ];

        yield [
            'Foobar',
            [
                [Token::T_LABEL, 'Foobar'],
            ]
        ];
        yield [
            'Foobar[]',
            [
                [Token::T_LABEL, 'Foobar'],
                [Token::T_LIST, '[]'],
            ]
        ];
        yield [
            'Foobar<Barfoo>',
            [
                [Token::T_LABEL, 'Foobar'],
                [Token::T_BRACKET_ANGLE_OPEN, '<'],
                [Token::T_LABEL, 'Barfoo'],
                [Token::T_BRACKET_ANGLE_CLOSE, '>'],
            ]
        ];
        yield [
            'Foobar<Barfoo>',
            [
                [Token::T_LABEL, 'Foobar'],
                [Token::T_BRACKET_ANGLE_OPEN, '<'],
                [Token::T_LABEL, 'Barfoo'],
                [Token::T_BRACKET_ANGLE_CLOSE, '>'],
            ]
        ];
        yield [
            'Foobar{Barfoo, Foobar}',
            [
                [Token::T_LABEL, 'Foobar'],
                [Token::T_BRACKET_CURLY_OPEN, '{'],
                [Token::T_LABEL, 'Barfoo'],
                [Token::T_COMMA, ','],
                [Token::T_WHITESPACE, ' '],
                [Token::T_LABEL, 'Foobar'],
                [Token::T_BRACKET_CURLY_CLOSE, '}'],
            ]
        ];
        yield [
            '"foobar"',
            [
                [Token::T_QUOTED_STRING, '"foobar"'],
            ]
        ];
        yield [
            '123',
            [
                [Token::T_INTEGER, '123'],
            ]
        ];

        yield [
            '123.4',
            [
                [Token::T_FLOAT, '123.4'],
            ]
        ];

        yield [
            'Foobar::FOOBAR_*',
            [
                [Token::T_LABEL, 'Foobar'],
                [Token::T_DOUBLE_COLON, '::'],
                [Token::T_LABEL, 'FOOBAR_*'],
            ]
        ];
        yield [
            'Foobar::*',
            [
                [Token::T_LABEL, 'Foobar'],
                [Token::T_DOUBLE_COLON, '::'],
                [Token::T_ASTERISK, '*'],
            ]
        ];
    }
}
