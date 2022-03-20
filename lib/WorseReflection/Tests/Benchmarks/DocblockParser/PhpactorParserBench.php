<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks\DocblockParser;

use Phpactor\WorseReflection\DocblockParser\Lexer;
use Phpactor\WorseReflection\DocblockParser\Parser;

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
