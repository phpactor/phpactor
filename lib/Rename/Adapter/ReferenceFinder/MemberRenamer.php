<?php

namespace Phpactor\Rename\Adapter\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLocator;

class MemberRenamer extends AbstractReferenceRenamer
{
    public function __construct(
        ReferenceFinder $referenceFinder,
        TextDocumentLocator $locator,
        Parser $parser,
        private ClassImplementationFinder $implementationFinder
    ) {
        parent::__construct($referenceFinder, $locator, $parser);
    }

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

        if ($node instanceof Parameter) {
            if ($node->visibilityToken === null) {
                return null;
            }

            return $this->offsetRangeFromToken($node->variableName, true);
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
        if ($node instanceof EnumCaseDeclaration) {
            return $this->offsetRangeFromToken($node->name, false);
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

    /**
     * @return Generator<LocatedTextEdit>
     */
    protected function doRename(TextDocument $textDocument, ByteOffset $offset, ByteOffsetRange $range, string $originalName, string $newName): Generator
    {
        foreach ($this->implementationFinder->findImplementations($textDocument, $offset, true) as $location) {
            yield $this->renameEdit($location, $range, $originalName, $newName);
        }

        yield from parent::doRename($textDocument, $offset, $range, $originalName, $newName);
    }
}
