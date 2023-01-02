<?php

namespace Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Node;

final class DocblockParser
{
    public function __construct(private Lexer $lexer, private Parser $parser)
    {
    }

    public static function create(): self
    {
        return new self(new Lexer(), new Parser());
    }

    public function parse(string $docblock): Docblock
    {
        return $this->parser->parse($this->lexer->lex($docblock));
    }
}
