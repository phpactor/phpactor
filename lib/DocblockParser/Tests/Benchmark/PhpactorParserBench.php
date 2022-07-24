<?php

namespace Phpactor\DocblockParser\Tests\Benchmark;

use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;

class PhpactorParserBench extends AbstractParserBenchCase
{
    private Parser $parser;

    private Lexer $lexer;

    public function setUp(): void
    {
        $this->parser = new Parser();
        $this->lexer = new Lexer();
    }

    public function parse(string $doc): void
    {
        $this->parser->parse($this->lexer->lex($doc));
    }
}
