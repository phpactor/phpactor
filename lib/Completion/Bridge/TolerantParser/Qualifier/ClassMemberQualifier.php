<?php

namespace Phpactor\Completion\Bridge\TolerantParser\Qualifier;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

/**
 * Returns true if either the node or the parent node is
 * a class member or scopeed (static) property access.
 */
class ClassMemberQualifier implements TolerantQualifier
{
    public function couldComplete(Node $node): ?Node
    {
        if ($this->isMemberNode($node)) {
            return $node;
        }

        if ($this->isMemberNode($node->parent)) {
            return $node->parent;
        }

        return null;
    }

    private function isMemberNode(?Node $node): bool
    {
        if (null === $node) {
            return false;
        }

        return
            $node instanceof MemberAccessExpression ||
            $node instanceof ScopedPropertyAccessExpression;
    }
}
