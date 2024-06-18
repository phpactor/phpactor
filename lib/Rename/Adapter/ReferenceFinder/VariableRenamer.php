<?php

namespace Phpactor\Rename\Adapter\ReferenceFinder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\TextDocument\ByteOffsetRange;

class VariableRenamer extends AbstractReferenceRenamer
{
    public function getRenameRangeForNode(Node $node): ?ByteOffsetRange
    {
        // do not try and rename static property names
        if ($node->parent instanceof ScopedPropertyAccessExpression) {
            return null;
        }

        if (
            $node instanceof Variable &&
            !$node->getFirstAncestor(PropertyDeclaration::class)
        ) {
            return $this->offsetRangeFromToken($node->name, true);
        }


        if ($node instanceof Parameter && $node->visibilityToken) {
            return null;
        }

        if (
            (
                $node instanceof Parameter ||
                $node instanceof UseVariableName ||
                $node instanceof CatchClause
            ) &&
            $node->variableName !== null
        ) {
            return $this->offsetRangeFromToken($node->variableName, true);
        }

        return null;
    }
}
