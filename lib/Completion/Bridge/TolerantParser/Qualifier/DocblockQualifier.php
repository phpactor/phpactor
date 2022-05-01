<?php

namespace Phpactor\Completion\Bridge\TolerantParser\Qualifier;

use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

class DocblockQualifier implements TolerantQualifier
{
    public function couldComplete(Node $node): ?Node
    {
        $docblock = $node->getLeadingCommentAndWhitespaceText();

        if (!preg_match('{@[a-z-]+}', $docblock)) {
            return null;
        }

        return $node;
    }
}
