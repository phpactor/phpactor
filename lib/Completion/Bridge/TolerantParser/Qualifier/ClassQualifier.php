<?php

namespace Phpactor\Completion\Bridge\TolerantParser\Qualifier;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

/**
 * Return true if the node is a candidate for class name completion.
 */
class ClassQualifier implements TolerantQualifier
{
    /**
     * @var int
     */
    private $minimumLength;

    public function __construct(int $minimumLength = 3)
    {
        $this->minimumLength = $minimumLength;
    }

    public function couldComplete(Node $node): ?Node
    {
        if (strlen($node->getText()) < $this->minimumLength) {
            return null;
        }

        if ($node instanceof QualifiedName) {
            return $node;
        }

        if ($node instanceof ObjectCreationExpression) {
            return $node;
        }

        if ($node instanceof NamespaceUseClause) {
            return $node;
        }

        if ($node instanceof NamespaceUseDeclaration) {
            return $node;
        }

        if ($node instanceof ClassBaseClause) {
            return $node;
        }

        return null;
    }
}
