<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Extension\Symfony\Model\SymfonyTemplateCache;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

final class SymfonyTemplateCompletor implements TolerantCompletor
{
    const ABSTRACT_CONTROLLER = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController';

    public function __construct(
        private Reflector $reflector,
        private SymfonyTemplateCache $templatePathCache
    ) {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!($node instanceof StringLiteral)) {
            return;
        }

        $callNode = $node->getFirstAncestor(CallExpression::class);

        if (!($callNode instanceof CallExpression)) {
            return;
        }

        $memberAccess = $callNode->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($callNode, $memberAccess->memberName);

        $allowedMethods = [
            'render',
            'renderView',
            'renderBlock',
            'renderBlockView',
        ];

        if (!in_array($methodName, $allowedMethods)) {
            return;
        }

        $argumentListNode = $callNode->getFirstDescendantNode(ArgumentExpressionList::class);

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getEndPosition())->nodeContext()->type();

        if ($containerType->instanceof(TypeFactory::class(self::ABSTRACT_CONTROLLER))->isFalseOrMaybe()) {
            return;
        }

        yield from $this->templatePathCache->completePath();
    }
}
