<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Microsoft\PhpParser\Node;
use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\AstProvider;
use function DeepCopy\deep_copy;

final class NodeAtCursorProvider
{
    public function __construct(private AstProvider $provider = new TolerantAstProvider())
    {
    }

    public function get(TextDocument $document, ByteOffset $byteOffset): Node
    {
        $node = deep_copy($this->provider->get($document));
        assert($node instanceof Node);
        $truncatedSourceCode = substr($document->__toString(), 0, $byteOffset->toInt());

        $lastNonWhiteSpaceOffset = OffsetHelper::lastNonWhitespaceByteOffset($truncatedSourceCode);

        if ($byteOffset->toInt() > $lastNonWhiteSpaceOffset) {
            $byteOffset = ByteOffset::fromInt($lastNonWhiteSpaceOffset);
        }

        $node = $node->getDescendantNodeAtPosition($byteOffset->toInt());

        $truncated = false;

        foreach ($node->getDescendantTokens() as $token) {

            // if the token finishes before the offset, then ignore it
            if ($token->getEndPosition() <= $byteOffset->toInt()) {
                continue;
            }

            // otherwise the token is the one that _contains_ the byte offset
            if (false === $truncated) {

                // truncate it up until the offset
                $token->length = $byteOffset->toInt() - $token->getFullStartPosition();
                $token->start = $token->fullStart;
                $truncated = true;
                continue;
            }

            // for all other tokens in the node, just truncate them
            $token->length = 0;
        }

        return $node;
    }
}
