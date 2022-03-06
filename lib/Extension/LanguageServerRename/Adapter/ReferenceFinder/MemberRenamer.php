<?php

namespace Phpactor\Extension\LanguageServerRename\Adapter\ReferenceFinder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\TextDocument\ByteOffsetRange;

class MemberRenamer extends AbstractReferenceRenamer
{
    public function getRenameRangeForNode(Node $node): ?ByteOffsetRange
    {
        if ($node instanceof MethodDeclaration) {
            return ByteOffsetRange::fromInts($node->name->start, $node->name->getEndPosition());
        }

        // hack because the WR property deefinition locator returns the
        // property declaration and not the variable
        if ($node instanceof PropertyDeclaration) {
            $variable = $node->getFirstDescendantNode(Variable::class);
            if (!$variable instanceof Variable) {
                return null;
            }
            return $this->offsetRangeFromToken($variable->name, true);
        }

        // hack because the WR property deefinition locator returns the
        // property declaration and not the variable
        if ($node instanceof ClassConstDeclaration) {
            $constElement = $node->getFirstDescendantNode(ConstElement::class);
            if (!$constElement instanceof ConstElement) {
                return null;
            }
            return $this->offsetRangeFromToken($constElement->name, false);
        }

        if ($node instanceof Variable && $node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->offsetRangeFromToken($node->name, true);
        }

        if (
            $node instanceof Variable &&
            (
                $node->getFirstAncestor(ScopedPropertyAccessExpression::class) ||
                $node->getFirstAncestor(MemberAccessExpression::class)
            )
        ) {
            return $this->offsetRangeFromToken($node->name, true);
        }

        if ($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression) {
            return $this->offsetRangeFromToken($node->memberName, false);
        }

        if ($node instanceof ConstElement) {
            return ByteOffsetRange::fromInts($node->name->start, $node->name->getEndPosition());
        }

        return null;
    }
}
