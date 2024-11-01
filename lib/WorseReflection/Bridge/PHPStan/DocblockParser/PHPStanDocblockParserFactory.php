<?php

namespace Phpactor\WorseReflection\Bridge\PHPStan\DocblockParser;

use PHPStan\PhpDocParser\Parser\ParserException;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Reflector;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

class PHPStanDocblockParserFactory implements DocBlockFactory
{
    private PhpDocParser $parser;

    public function __construct(
        private Reflector $reflector,
        private Lexer $lexer = new Lexer(),
        ?PhpDocParser $parser = null,
    ) {
        $parser ??= new PhpDocParser(new TypeParser(new ConstExprParser()), new ConstExprParser());
        $this->parser = $parser;
    }

    public function create(string $docblock, ReflectionScope $scope): DocBlock
    {
        $docblock = $this->sanitizeDocblock($docblock);
        try {
            $node = $this->parser->parse(new TokenIterator($this->lexer->tokenize($docblock)));
        } catch (ParserException) {
            return new PlainDocblock($docblock);
        }

        return new PHPStanParsedDocblock(
            $node,
            new PHPStanTypeConverter($this->reflector, $scope),
            $docblock
        );
    }

    /**
     * phpstan/docblock-parser is pretty strict about the doc it parses -- any short comments or
     * excessive new lines lead to parser exception, so try to get rid of them before parsing.
     */
    private function sanitizeDocblock(string $docblock): string
    {
        $docblock = preg_replace('~\h*\/\/.*~', '', $docblock) ?? $docblock;
        return trim($docblock);
    }
}
