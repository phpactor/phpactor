<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

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
    ) {
        $constExprParser = new ConstExprParser();
        $typeParser = new TypeParser($constExprParser);
        $this->parser = new PhpDocParser($typeParser, $constExprParser);
    }

    public function create(string $docblock, ReflectionScope $scope): DocBlock
    {
        if (trim($docblock) === '') {
            return new PlainDocblock();
        }

        $node = $this->parser->parse(new TokenIterator($this->lexer->tokenize($docblock)));
        return new PHPStanParsedDocblock(
            $node,
            new PHPStanTypeConverter($this->reflector, $scope),
            $docblock
        );
    }
}
