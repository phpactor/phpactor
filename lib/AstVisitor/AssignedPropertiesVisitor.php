<?php

namespace Phpactor\AstVisitor;

use PhpParser\NodeVisitor;
use PhpParser\Node\Expr;

class AssignedPropertiesVisitor implements NodeVisitor
{
    private $assignedProperties = [];

    /**
     * {@inheritDoc}
     */
    public function beforeTraverse(array $nodes)
    {
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function enterNode(\PhpParser\Node $node)
    {
        if (!$node instanceof Expr\Assign) {
            return;
        }

        if (!$node->var instanceof Expr\PropertyFetch) {
            return;
        }

        if (!isset($node->var->var)) {
            return;
        }

        // we only care about direct assignations.
        // e.g. $this->foo = 'bar' and not $this->bar->foo = 'bar'
        if ($node->var->var->name !== 'this') {
            return;
        }

        $this->assignedProperties[] = $node;
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function leaveNode(\PhpParser\Node $node)
    {
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function afterTraverse(array $nodes)
    {
    }

    public function getAssignedPropertyNodes()
    {
        return $this->assignedProperties;
    }
}
