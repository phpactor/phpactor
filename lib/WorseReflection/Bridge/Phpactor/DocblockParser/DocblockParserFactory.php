<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Docblock as ParserDocblock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Reflector;

class DocblockParserFactory implements DocBlockFactory
{
    const SUPPORTED_TAGS = [
        '@property',
        '@var',
        '@psalm-var',
        '@phpstan-var',
        '@param',
        '@psalm-param',
        '@phpstan-param',
        '@return',
        '@psalm-return',
        '@phpstan-return',
        '@method',
        '@psalm-method',
        '@phpstan-method',
        '@deprecated',
        '@extends',
        '@psalm-extends',
        '@phpstan-extends',
        '@implements',
        '@psalm-implements',
        '@phpstan-implements',
        '@template',
        '@template-covariant',
        '@psalm-template',
        '@phpstan-template',
        '@template-extends',
        '@psalm-template-extends',
        '@phpstan-template-extends',
        '@mixin',
        '@throws',
    ];

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
        if (empty(trim($docblock))) {
            return new PlainDocblock();
        }

        // if no supported tags in the docblock, do not parse it
        if (0 === preg_match(
            sprintf('{(%s)}', implode('|', self::SUPPORTED_TAGS)),
            $docblock,
            $matches
        )) {
            return new PlainDocblock($docblock);
        }

        $node = $this->parser->parse($this->lexer->lex($docblock));
        assert($node instanceof ParserDocblock);
        return new ParsedDocblock(
            $node,
            new TypeConverter($this->reflector, $scope),
            $docblock
        );
    }
}
