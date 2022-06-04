<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
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
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayKeyType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class AssignmentToMissingPropertyProvider implements DiagnosticProvider
{
    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
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

        try {
            $class = $resolver->reflector()->reflectClassLike($classNode->getNamespacedName()->__toString());
        } catch (NotFound $notFound) {
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
            $this->resolvePropertyType($resolver, $frame, $rightOperand, $accessExpression),
            $accessExpression ? true : false,
        );
    }

    private function resolvePropertyType(
        NodeContextResolver $resolver,
        Frame $frame,
        Expression $rightOperand,
        ?Node $accessExpression
    ): Type {
        $type = $resolver->resolveNode($frame, $rightOperand)->type();

        if (!$accessExpression) {
            return $type;
        }

        return new ArrayType(
            $accessExpression instanceof SubscriptExpression ? new ArrayKeyType() : $resolver->resolveNode($frame, $accessExpression)->type(),
            $type
        );
    }
}
