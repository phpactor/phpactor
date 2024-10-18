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
    private const SUPPORTED_TAGS = [
        'assert',
        'deprecated',
        'extends',
        'implements',
        'method',
        'mixin',
        'param',
        'property',
        'return',
        'template',
        'template-covariant',
        'template-extends',
        'throws',
        'type',
        'var',
    ];

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
        if (trim($docblock) === '') {
            return new PlainDocblock();
        }

        // if no supported tags in the docblock, do not parse it
        if (0 === preg_match(
            sprintf('{@((psalm|phpstan|phan)-)?(%s)}', implode('|', self::SUPPORTED_TAGS)),
            $docblock,
            $matches
        )) {
            return new PlainDocblock($docblock);
        }

        try {
            $node = $this->parser->parse(new TokenIterator($this->lexer->tokenize(trim($docblock))));
        } catch (ParserException) {
            return new PlainDocblock($docblock);
        }

        return new PHPStanParsedDocblock(
            $node,
            new PHPStanTypeConverter($this->reflector, $scope),
            $docblock
        );
    }
}
