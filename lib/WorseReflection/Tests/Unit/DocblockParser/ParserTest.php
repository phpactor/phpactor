<?php

namespace Phpactor\WorseReflection\Tests\Unit\DocblockParser;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\DocblockParser\Ast\Docblock;
use Phpactor\WorseReflection\DocblockParser\Ast\Node;
use Phpactor\WorseReflection\DocblockParser\Lexer;
use Phpactor\WorseReflection\DocblockParser\Parser;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;

class ParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(string $text, Node $expected): void
    {
        $node = (new Parser())->parse((new Lexer())->lex($text));
        self::assertEquals($expected, $node);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '/** */',
            new Docblock([
                new Token(0, Token::T_PHPDOC_OPEN, '/**'),
                new Token(3, Token::T_WHITESPACE, ' '),
                new Token(4, Token::T_PHPDOC_CLOSE, '*/'),
            ])
        ];
        yield [
            '/** Hello */',
            new Docblock([
                new Token(0, Token::T_PHPDOC_OPEN, '/**'),
                new Token(3, Token::T_WHITESPACE, ' '),
                new Token(4, Token::T_LABEL, 'Hello'),
                new Token(9, Token::T_WHITESPACE, ' '),
                new Token(10, Token::T_PHPDOC_CLOSE, '*/'),
            ])
        ];
    }
}
