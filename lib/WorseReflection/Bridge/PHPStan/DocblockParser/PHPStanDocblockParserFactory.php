<?php

namespace Phpactor\WorseReflection\Bridge\PHPStan\DocblockParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\ParserConfig;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Reflector;

class PHPStanDocblockParserFactory implements DocBlockFactory
{
    private PhpDocParser $parser;

    private Lexer $lexer;

    public function __construct(
        private Reflector $reflector,
        ?Lexer $lexer = null,
        ?PhpDocParser $parser = null,
    ) {
        $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
        $this->lexer = $lexer ?? new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);
        $this->parser = $parser ?? new PhpDocParser($config, $typeParser, $constExprParser);
    }

    public function create(string $docblock, ReflectionScope $scope): DocBlock
    {
        $docblock = $this->sanitizeDocblock($docblock);
        $node = new PhpDocNode([]);
        try {
            $tokens = $this->lexer->tokenize($docblock);
            $docblockBeginnings = array_filter(
                $tokens,
                /** @param array{string, int, int} $token */
                static fn (array $token) => $token[1] === Lexer::TOKEN_OPEN_PHPDOC
            );
            // _force_ phpstan to iterate all docblocks found in current fragment
            foreach ($docblockBeginnings as $i => $docblockBeginning) {
                $iterator = new TokenIterator($tokens, $i);
                array_push($node->children, ...$this->parser->parse($iterator)->children);
            }
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
