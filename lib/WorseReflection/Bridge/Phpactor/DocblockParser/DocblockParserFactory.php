<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Docblock as ParserDocblock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Reflector;

class DocblockParserFactory implements DocBlockFactory
{
    private Lexer $lexer;

    private Parser $parser;

    public function __construct(
        private Reflector $reflector,
        ?Lexer $lexer = null,
        ?Parser $parser = null
    ) {
        $this->lexer = $lexer ?: new Lexer();
        $this->parser = $parser ?: new Parser();
    }

    public function create(string $docblock, ReflectionScope $scope): DocBlock
    {
        $node = $this->parser->parse($this->lexer->lex($docblock));
        assert($node instanceof ParserDocblock);
        return new ParsedDocblock(
            $node,
            new TypeConverter($this->reflector, $scope),
            $docblock
        );
    }
}
