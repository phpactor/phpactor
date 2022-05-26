<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

class MissingMethods implements DiagnosticProvider
{
    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
    {
        if ((!$node instanceof CallExpression)) {
            return;
        }

        $memberName = null;
        if ($node->callableExpression instanceof MemberAccessExpression) {
            $memberName = $node->callableExpression->memberName;
        } elseif ($node->callableExpression instanceof ScopedPropertyAccessExpression) {
            $memberName = $node->callableExpression->memberName;
        }

        if (!($memberName instanceof Token)) {
            return;
        }

        $containerType = $resolver->resolveNode($frame, $node)->containerType();

        if (!$containerType->isDefined()) {
            return;
        }

        if (!$containerType instanceof ReflectedClassType) {
            return;
        }

        $class = $containerType->reflectionOrNull();
        if (!$class) {
            return;
        }

        $methodName = $memberName->getText($node->getFileContents());
        if (!is_string($methodName)) {
            return;
        }
        try {
            $name = $class->methods()->get($methodName);
        } catch (NotFound $notFound) {
            yield new Diagnostic(
                ByteOffsetRange::fromInts(
                    $memberName->getStartPosition(),
                    $memberName->getEndPosition()
                ),
                sprintf(
                    'Method "%s" does not exist on class "%s"',
                    $methodName,
                    $containerType->__toString()
                ),
                Diagnostic::ERROR
            );
        }
    }
}
