<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class AssignmentToMissingPropertyProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof AssignmentExpression) {
            return;
        }

        $memberAccess = $node->leftOperand;
        $accessExpression = null;
        if ($memberAccess instanceof SubscriptExpression) {
            /** @phpstan-ignore-next-line Access expression is NULL if list addition */
            $accessExpression = $memberAccess->accessExpression ?: $memberAccess;
            $memberAccess = $memberAccess->postfixExpression;
        }

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $deref = $memberAccess->dereferencableExpression;

        if (!$deref instanceof Variable) {
            return;
        }

        if ($deref->getText() !== '$this') {
            return;
        }

        $memberNameToken = $memberAccess->memberName;

        if (!$memberNameToken instanceof Token) {
            return;
        }

        $memberName = $memberNameToken->getText($node->getFileContents());

        if (!is_string($memberName)) {
            return;
        }

        $rightOperand = $node->rightOperand;

        if (!$rightOperand instanceof Expression) {
            return;
        }

        $classNode = NodeUtil::nodeContainerClassLikeDeclaration($node);

        if (null === $classNode) {
            return;
        }

        try {
            $class = $resolver->reflector()->reflectClassLike($classNode->getNamespacedName()->__toString());
        } catch (NotFound) {
            return;
        }

        if (!$class instanceof ReflectionTrait && !$class instanceof ReflectionClass) {
            return;
        }

        if ($class->properties()->has($memberName)) {
            return;
        }

        yield new AssignmentToMissingPropertyDiagnostic(
            ByteOffsetRange::fromInts(
                $node->getStartPosition(),
                $node->getEndPosition()
            ),
            $class->name()->__toString(),
            $memberName,
            $this->resolvePropertyType($resolver, $frameStack, $rightOperand, $accessExpression),
            $accessExpression ? true : false,
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    private function resolvePropertyType(
        NodeContextResolver $resolver,
        FrameStack $frameStack,
        Expression $rightOperand,
        Node|MissingToken|null $accessExpression
    ): Type {
        $type = $resolver->resolveNode($frameStack, $rightOperand)->type();

        if (!$accessExpression instanceof Node) {
            return $type;
        }

        return new ArrayType(
            $accessExpression instanceof SubscriptExpression ? null : $resolver->resolveNode($frameStack, $accessExpression)->type(),
            $type
        );
    }
}
