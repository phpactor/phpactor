<?php

namespace Phpactor\Completion\Bridge\TolerantParser\Qualifier;

use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

class AlwaysQualfifier implements TolerantQualifier
{
    /**
     * {@inheritDoc}
     */
    public function couldComplete(Node $node): ?Node
    {
        return $node;
    }
}
