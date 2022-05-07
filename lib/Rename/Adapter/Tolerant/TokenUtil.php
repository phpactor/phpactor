<?php

namespace Phpactor\Rename\Adapter\Tolerant;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\ByteOffsetRange;

final class TokenUtil
{
    /**
     * @param Token|Node $tokenOrNode
     */
    public static function offsetRangeFromToken($tokenOrNode, bool $hasDollar): ?ByteOffsetRange
    {
        if ($tokenOrNode instanceof Node) {
            return ByteOffsetRange::fromInts($tokenOrNode->getStartPosition(), $tokenOrNode->getEndPosition());
        }

        if (!$tokenOrNode instanceof Token) {
            return null;
        }

        if ($hasDollar) {
            return ByteOffsetRange::fromInts($tokenOrNode->start + 1, $tokenOrNode->getEndPosition());
        }

        return ByteOffsetRange::fromInts($tokenOrNode->start, $tokenOrNode->getEndPosition());
    }
}
