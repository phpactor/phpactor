<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Extension\Symfony\Model\FormTypeCompletionCache;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

final class SymfonyFormTypeCompletor implements TolerantCompletor
{
    const FORM_BUILDER_INTERFACE = 'Symfony\\Component\\Form\\FormBuilderInterface';

    public function __construct(private Reflector $reflector)
    {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!($node instanceof StringLiteral)) {
            return;
        }

        if (!($node->parent instanceof ArrayElement)) {
            return;
        }

        $arrayElementNode = $node->parent;

        // we need to check if we're on the LHS or RHS of the array
        $arrayChildNodes = $arrayElementNode->getChildNodes();
        $arrayChildNodes->next();

        $isLHS = $arrayChildNodes->current() !== $node;

        if (!$isLHS) {
            return;
        }

        if (!($arrayElementNode->parent instanceof ArrayElementList)) {
            return;
        }

        $callNode = $arrayElementNode->getFirstAncestor(CallExpression::class);
        if (!($callNode instanceof CallExpression)) {
            return;
        }

        $argumentListNode = $callNode->getFirstDescendantNode(ArgumentExpressionList::class);

        if (!($argumentListNode instanceof ArgumentExpressionList)) {
            return;
        }

        $memberAccess = $callNode->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($callNode, $memberAccess->memberName);

        if ($methodName !== 'add') {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getEndPosition())->nodeContext()->type();

        if ($containerType->instanceof(TypeFactory::class(self::FORM_BUILDER_INTERFACE))->isFalseOrMaybe()) {
            return;
        }

        $generator = $argumentListNode->getChildNodes();
        $generator->next();

        $formTypeNode = $generator->current();

        if (!($formTypeNode instanceof ArgumentExpression)) {
            return;
        }

        $formTypeClassType = $this->reflector->reflectOffset($source, $formTypeNode->getEndPosition())->nodeContext()->type();

        if (!($formTypeClassType instanceof ClassStringType)) {
            return;
        }

        $formTypeClassFQN = $formTypeClassType->className()?->full();

        if ($formTypeClassFQN === null) {
            return;
        }

        yield from FormTypeCompletionCache::complete($formTypeClassFQN);
    }
}
