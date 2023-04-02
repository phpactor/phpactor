<?php

namespace Phpactor\DocblockParser\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Ast\Tag\ParamTag;
use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;
use Phpactor\DocblockParser\Ast\Token;

class ParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(string $text, Node $expected): void
    {
        self::assertEquals($expected, $this->parse($text));
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

    /**
     * @dataProvider provideMultidimensionalType
     */
    public function testParseMultidimensionalArray(string $type, ?string $expectedType = null): void
    {
        $expectedType = $expectedType ?? $type;
        $node = $this->parse(sprintf('/** @param %s $doc */', $type));
        self::assertInstanceOf(Docblock::class, $node);

        $paramTag =$node->children->toArray()[2];
        self::assertInstanceOf(ParamTag::class, $paramTag);
        self::assertEquals($expectedType, $paramTag->type?->toString());
        self::assertEquals('$doc', $paramTag->variable?->toString());
    }

    /**
     * @return Generator<string,array{string}|array{string,string}>
     */
    public function provideMultidimensionalType(): Generator
    {
        yield 'One dimension' => ['string[]'];
        yield 'Two dimensions' => ['string[][]'];
        yield 'Three dimensions' => ['string[][][]'];
        yield 'Different types of syntax' => ['array<string>[]'];

        yield 'Array shapes' => [
            'array{first_name: string, last_name: string}[]',
            '{first_name: string, last_name: string}[]'
        ];
    }
    private function parse(string $text): Node
    {
        return (new Parser())->parse((new Lexer())->lex($text));
    }
}
