<?php

namespace Phpactor\Completion\Bridge\TolerantParser\Qualifier;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\AttributeGroup;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

class AttributeQualifier implements TolerantQualifier
{
    public function couldComplete(Node $node): ?Node
    {
        if ($node instanceof AttributeGroup) {
            return $node;
        }

        if ($node instanceof Attribute) {
            return $node;
        }

        if ($node->parent instanceof Attribute) {
            return $node->parent;
        }

        return null;
    }
}
