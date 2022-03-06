<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\Helper\InterestingOffsetFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class WorseInterestingOffsetFinder implements InterestingOffsetFinder
{
    /**
     * @var SourceCodeReflector
     */
    private $reflector;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(SourceCodeReflector $reflector, Parser $parser = null)
    {
        $this->reflector = $reflector;
        $this->parser = $parser ?: new Parser();
    }

    public function find(TextDocument $source, ByteOffset $offset): ByteOffset
    {
        if ($interestingOffset = $this->resolveInterestingOffset($source, $offset)) {
            return $interestingOffset;
        }

        $node = $this->parser->parseSourceFile($source->__toString())->getDescendantNodeAtPosition($offset->toInt());

        do {
            $offset = ByteOffset::fromInt($node->getStartPosition());

            if ($interestingOffset = $this->resolveInterestingOffset($source, $offset)) {
                return $interestingOffset;
            }

            $node = $node->parent;
        } while ($node);

        return $offset;
    }

    private function resolveInterestingOffset(TextDocument $source, ByteOffset $offset): ?ByteOffset
    {
        $reflectionOffset = $this->reflector->reflectOffset($source, $offset->toInt());

        $symbolType = $reflectionOffset->symbolContext()->symbol()->symbolType();

        if ($symbolType !== Symbol::UNKNOWN) {
            return $offset;
        }

        return null;
    }
}
