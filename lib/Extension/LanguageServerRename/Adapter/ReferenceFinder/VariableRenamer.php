<?php

namespace Phpactor\Extension\LanguageServerRename\Adapter\ReferenceFinder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\TextDocument\ByteOffsetRange;

class VariableRenamer extends AbstractReferenceRenamer
{
    public function getRenameRangeForNode(Node $node): ?ByteOffsetRange
    {
        if (
            $node instanceof Variable &&
            !$node->getFirstAncestor(PropertyDeclaration::class)
        ) {
            return $this->offsetRangeFromToken($node->name, true);
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
