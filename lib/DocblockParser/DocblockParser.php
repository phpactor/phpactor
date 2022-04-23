<?php

namespace Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Node;

final class DocblockParser
{
    private Lexer $lexer;

    private Parser $parser;

    public function __construct(Lexer $lexer, Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }

    public static function create(): self
    {
        return new self(new Lexer(), new Parser());
    }

    public function parse(string $docblock): Node
    {
        return $this->parser->parse($this->lexer->lex($docblock));
    }
}
