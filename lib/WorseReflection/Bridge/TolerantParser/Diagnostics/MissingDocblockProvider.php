<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MethodDeclarationResolver;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MissingDocblockProvider implements DiagnosticProvider
{
    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        $declaration = NodeUtil::nodeContainerClassLikeDeclaration($node);

        try {
            $class = $resolver->reflector()->reflectClassLike($declaration->getNamespacedName()->__toString());
            $methodName = $node->name->getText($node->getFileContents());
            if (!is_string($methodName)) {
                return;
            }
            $method = $class->methods()->get($methodName);
        } catch (NotFound $notFound) {
            return;
        }

        $docblock = $method->docblock();
        $docblockType = $docblock->returnType();
        $actualReturnType = $frame->returnType()->generalize();
        $claimedReturnType = $method->inferredType();
        $phpReturnType = $method->type();

        // if there is already a docblock, ignore
        if ($method->docblock()->isDefined()) {
            return;
        }

        // do not try it for overriden methods
        if ($method->original()->declaringClass()->name() != $class->name()) {
            return;
        }

        if ($method->name() === '__construct') {
            return;
        }

        // it's void
        if (false === $actualReturnType->isDefined()) {
            return;
        }

        if (
            $claimedReturnType->isDefined() && !$claimedReturnType->isClass() && !$claimedReturnType->isArray() && !$claimedReturnType->isClosure()
        ) {
            return;
        }

        if ($actualReturnType->isClosure()) {
            $methods[] = $method;
            return;
        }

        if ($claimedReturnType->isClass() && !$actualReturnType instanceof GenericClassType) {
            return;
        }

        if ($claimedReturnType->isArray() && $actualReturnType->isMixed()) {
            return;
        }

        // the docblock matches the generalized return type
        // it's OK
        if ($claimedReturnType->equals($actualReturnType)) {
            return;
        }

        yield new MissingDocblockDiagnostic(
            ByteOffsetRange::fromInts(
                $node->getStartPosition(),
                $node->getEndPosition()
            ),
            sprintf(
                'Method "%s" is missing docblock return type: %s',
                $methodName,
                $actualReturnType->__toString(),
            ),
            DiagnosticSeverity::WARNING(),
            $class->name()->__toString(),
            $methodName,
            $actualReturnType->__toString(),
        );
    }
}
