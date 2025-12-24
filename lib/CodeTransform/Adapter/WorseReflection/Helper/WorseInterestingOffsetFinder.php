<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Domain\Helper\InterestingOffsetFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class WorseInterestingOffsetFinder implements InterestingOffsetFinder
{
    public function __construct(
        private SourceCodeReflector $reflector,
        private AstProvider $parser = new TolerantAstProvider(),
    ) {
    }

    public function find(TextDocument $source, ByteOffset $offset): ByteOffset
    {
        if ($interestingOffset = $this->resolveInterestingOffset($source, $offset)) {
            return $interestingOffset;
        }

        $node = $this->parser->get($source)->getDescendantNodeAtPosition($offset->toInt());

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

        $symbolType = $reflectionOffset->nodeContext()->symbol()->symbolType();

        if ($symbolType !== Symbol::UNKNOWN) {
            return $offset;
        }

        return null;
    }
}
