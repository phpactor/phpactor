<?php

namespace Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Docblock;
use RuntimeException;

final class DocblockParser
{
    public function __construct(
        private readonly Lexer $lexer,
        private readonly Parser $parser
    ) {
    }

    public static function create(): self
    {
        return new self(new Lexer(), new Parser());
    }

    public function parse(string $docblock): Docblock
    {
        $node = $this->parser->parse($this->lexer->lex($docblock));
        if (!$node instanceof Docblock) {
            throw new RuntimeException(sprintf(
                'Expected a Docblock node from parser, but got "%s"',
                get_class($node)
            ));
        }

        return $node;
    }
}
