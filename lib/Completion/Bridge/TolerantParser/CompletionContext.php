<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;

class CompletionContext
{
    public static function expression(Node $node): bool
    {
        $parent = $node->parent;
        return
            $parent instanceof Expression ||
            $parent instanceof ExpressionStatement ||
            $parent instanceof IfStatementNode ||
            $parent instanceof ArrayElement // yield;
        ;
    }
}
